<?php

namespace App\Services;

use App\Models\Service;
use App\Support\ServiceImageStorage;
use Closure;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class TemporaryServiceImages
{
    public const ID_PATTERN = '/\A[a-f0-9]{64}\z/';

    // Claimed files are transferred to the worker's directory before dispatch.
    private const DIRECTORY = 'temporary-service-images';
    private const RETENTION_HOURS = 24;

    public function upload(Request $request, ?Service $service = null): JsonResponse
    {
        $request->validate(['image' => 'required|' . ServiceImageStorage::UPLOAD_RULES]);

        if (count($request->allFiles()) !== 1) {
            throw ValidationException::withMessages(['image' => 'Trimite o singură poză pe cerere.']);
        }

        $image = $request->file('image');
        $digest = hash_file('sha256', $image->getPathname());
        abort_if($digest === false, 503, 'Poza nu a putut fi citită. Reîncearcă.');

        $directory = $this->sessionDirectory($request);
        $scope = $this->scope($service);
        $id = hash_hmac('sha256', $scope . ':' . $digest, basename($directory));
        $filename = $scope . '_' . $id;
        $path = $directory . '/' . $filename;
        $disk = Storage::disk('local');

        return $this->locked($directory, function () use ($disk, $directory, $image, $id, $filename, $path) {
            $files = $this->removeExpired($disk, $directory);
            if (in_array($path, $files, true)) {
                return response()->json(['id' => $id]);
            }

            if (count($files) >= ServiceImageStorage::MAX_IMAGES) {
                throw ValidationException::withMessages([
                    'image' => 'Poți avea maxim ' . ServiceImageStorage::MAX_IMAGES . ' poze în sesiunea de încărcare.',
                ]);
            }

            // Publish the staged file atomically so a failed write cannot be a valid retry.
            $staged = $disk->putFileAs($directory, $image, $filename . '.part');
            if (!$staged || !$disk->move($staged, $path)) {
                $disk->delete($path . '.part');
                abort(503, 'Poza nu a putut fi salvată. Reîncearcă.');
            }

            return response()->json(['id' => $id], 201);
        });
    }

    public function availableIds(Request $request, array $ids, ?Service $service = null): array
    {
        $disk = Storage::disk('local');
        $directory = $this->sessionDirectory($request);

        return array_values(array_unique(array_filter(
            array_slice($ids, 0, ServiceImageStorage::MAX_IMAGES),
            fn ($id) => $this->stagedPath($disk, $directory, $this->scope($service), $id) !== null,
        )));
    }

    public function preview(Request $request, ?Service $service = null): BinaryFileResponse
    {
        $disk = Storage::disk('local');
        $path = $this->stagedPath($disk, $this->sessionDirectory($request), $this->scope($service), $request->query()['temporary_image'] ?? null);
        $headers = ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'];
        abort_if($path === null, 404, '', $headers);

        return response()->file($disk->path($path), $headers);
    }

    public function discard(Request $request, ?Service $service = null): JsonResponse
    {
        $validated = $request->validate(['remove_temporary' => ['required', 'string', 'regex:' . self::ID_PATTERN]]);
        $directory = $this->sessionDirectory($request);

        return $this->locked($directory, function () use ($directory, $validated, $service) {
            $disk = Storage::disk('local');
            $path = $directory . '/' . $this->scope($service) . '_' . $validated['remove_temporary'];
            abort_unless($disk->delete($path), 503, 'Poza nu a putut fi eliminată. Reîncearcă.');

            return response()->json(null, 204);
        });
    }

    public function attach(Request $request, array $ids, Closure $saveListing, ?Service $scopeService = null): array
    {
        $directory = $this->sessionDirectory($request);
        $staging = Storage::disk('local');
        $queue = Storage::disk();
        $scope = $this->scope($scopeService);
        $batch = $scopeService ? '/' . Str::uuid() : '';

        return $this->locked($directory, function () use ($directory, $ids, $staging, $queue, $saveListing, $scope, $batch) {
            $sources = array_map(fn ($id) => $this->stagedPath($staging, $directory, $scope, $id), $ids);
            if (in_array(null, $sources, true)) {
                throw ValidationException::withMessages([
                    ($batch ? 'gallery_order' : 'temporary_image_ids') => 'Unele poze nu mai sunt disponibile. Alege din nou pozele care lipsesc.',
                ]);
            }

            $transferred = [];
            try {
                [$service, $result] = DB::transaction(function () use ($saveListing, $sources, $staging, $queue, $batch, &$transferred) {
                    [$service, $result] = $saveListing();
                    foreach ($sources as $source) {
                        $destination = 'service-image-queue/' . $service->id . $batch . '/' . basename($source);
                        $this->transferToQueue($staging, $queue, $source, $destination);
                        $transferred[$source] = $destination;
                    }

                    return [$service, $result];
                });
            } catch (Throwable $exception) {
                foreach ($transferred as $source => $destination) {
                    if ($staging === $queue) {
                        if (!$staging->move($destination, $source)) {
                            report(new RuntimeException('Could not restore a staged listing image after rollback.'));
                        }
                    } else {
                        $queue->delete($destination);
                    }
                }
                throw $exception;
            }

            if ($staging !== $queue) {
                $staging->delete($sources);
            }

            return [$service, $result, array_values($transferred)];
        });
    }

    private function transferToQueue(FilesystemAdapter $staging, FilesystemAdapter $queue, string $source, string $destination): void
    {
        if ($staging === $queue) {
            abort_unless($staging->move($source, $destination), 503, 'Poza nu a putut fi atașată. Reîncearcă.');
            return;
        }

        $stream = $staging->readStream($source);
        abort_unless(is_resource($stream), 503, 'Poza nu a putut fi citită. Reîncearcă.');
        try {
            if (!$queue->put($destination, $stream)) {
                $queue->delete($destination);
                abort(503, 'Poza nu a putut fi atașată. Reîncearcă.');
            }
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    private function sessionDirectory(Request $request): string
    {
        return self::DIRECTORY . '/' . hash('sha256', $request->session()->getId());
    }

    private function scope(?Service $service): string
    {
        return $service ? 'edit-' . $service->getKey() : 'publish';
    }

    private function stagedPath(FilesystemAdapter $disk, string $directory, string $scope, mixed $id): ?string
    {
        if (!is_string($id) || !preg_match(self::ID_PATTERN, $id)) {
            return null;
        }

        $path = $directory . '/' . $scope . '_' . $id;

        return $disk->exists($path) && $disk->lastModified($path) > $this->expirationCutoff() ? $path : null;
    }

    private function locked(string $directory, Closure $callback): mixed
    {
        try {
            return Cache::lock($directory, 120)->block(5, $callback);
        } catch (LockTimeoutException) {
            abort(503, 'Încărcarea este ocupată. Reîncearcă.');
        }
    }

    public function cleanup(): void
    {
        $disk = Storage::disk('local');

        foreach ($disk->directories(self::DIRECTORY) as $directory) {
            if (!preg_match('/\A[a-f0-9]{64}\z/', basename($directory))) {
                continue;
            }

            Cache::lock($directory, 120)->get(function () use ($disk, $directory) {
                $this->removeExpired($disk, $directory);
                if ($disk->files($directory) === [] && $disk->directories($directory) === []) {
                    $disk->deleteDirectory($directory);
                }
            });
        }
    }

    private function removeExpired(FilesystemAdapter $disk, string $directory): array
    {
        $cutoff = $this->expirationCutoff();
        $active = [];

        foreach ($disk->files($directory) as $path) {
            if (!preg_match('/\A(?:publish|edit-\d+)_[a-f0-9]{64}(?:\.part)?\z/', basename($path))) {
                continue;
            }

            if ($disk->lastModified($path) <= $cutoff) {
                if (!$disk->delete($path)) {
                    throw new RuntimeException('Could not delete an expired temporary listing image.');
                }
                continue;
            }

            if (!str_ends_with($path, '.part')) {
                $active[] = $path;
            }
        }

        return $active;
    }

    private function expirationCutoff(): int
    {
        return now()->subHours(self::RETENTION_HOURS)->getTimestamp();
    }
}
