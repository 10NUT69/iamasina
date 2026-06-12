<?php

namespace App\Support;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;
use Illuminate\Support\Facades\File;

class ManualMediaBackupArchiver
{
    public const EXPORT_DIRECTORY_RELATIVE = 'backups/media-exports';

    private const MEDIA_ARCHIVE_ROOTS = ['services', 'dealers'];
    private const ALLOWED_MEDIA_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];

    public static function available(): bool
    {
        if (config()->has('backups.force_media_export_available')) {
            return (bool) config('backups.force_media_export_available');
        }

        return (bool) self::findExecutable('zip') || class_exists(ZipArchive::class);
    }

    public static function exportDirectory(): string
    {
        $directory = config(
            'backups.media_export_directory',
            storage_path('app/private/' . str_replace('/', DIRECTORY_SEPARATOR, self::EXPORT_DIRECTORY_RELATIVE))
        );

        File::ensureDirectoryExists($directory);

        return $directory;
    }

    public static function archiveRelativePath(string $filename): string
    {
        return self::EXPORT_DIRECTORY_RELATIVE . '/' . $filename;
    }

    public static function resolveExportPath(string $relativePath): ?string
    {
        $relativePath = str_replace('\\', '/', trim($relativePath));
        $relativePath = ltrim($relativePath, '/');

        if (
            $relativePath === ''
            || str_contains($relativePath, '../')
            || str_contains($relativePath, '/..')
            || preg_match('/^[a-zA-Z]:/', $relativePath)
            || ! str_starts_with($relativePath, self::EXPORT_DIRECTORY_RELATIVE . '/')
        ) {
            return null;
        }

        $prefix = self::EXPORT_DIRECTORY_RELATIVE . '/';
        $filename = substr($relativePath, strlen($prefix));

        if ($filename === '' || $filename !== basename($filename)) {
            return null;
        }

        $path = self::exportDirectory() . DIRECTORY_SEPARATOR . $filename;

        try {
            self::ensurePathIsInside($path, self::exportDirectory());
        } catch (RuntimeException) {
            return null;
        }

        return $path;
    }

    public static function mediaDirectories(): array
    {
        $configuredDirectories = config('backups.media_directories');

        if (is_array($configuredDirectories) && $configuredDirectories !== []) {
            return $configuredDirectories;
        }

        return [
            'services' => storage_path('app/public/services'),
            'dealers' => storage_path('app/public/dealers'),
        ];
    }

    public function create(string $zipPath, int $timeoutSeconds = 7200): void
    {
        File::ensureDirectoryExists(dirname($zipPath));
        $this->ensureMediaDirectoriesExist();

        $zipExecutable = self::findExecutable('zip');

        if ($zipExecutable && $this->createWithCliZip($zipExecutable, $zipPath, $timeoutSeconds)) {
            return;
        }

        $this->createWithZipArchive($zipPath);
    }

    private function createWithCliZip(string $zipExecutable, string $zipPath, int $timeoutSeconds): bool
    {
        @unlink($zipPath);

        $command = array_merge(
            [$zipExecutable, '-0', '-r', $zipPath],
            array_keys(self::mediaDirectories()),
            ['-i'],
            $this->zipIncludePatterns(),
        );

        $process = new Process($command, $this->publicStorageDirectory());
        $process->setTimeout($timeoutSeconds);
        $process->run();

        if ($process->isSuccessful() && is_file($zipPath) && filesize($zipPath) > 0) {
            return true;
        }

        @unlink($zipPath);

        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('zip_cli_failed');
        }

        return false;
    }

    private function createWithZipArchive(string $zipPath): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('zip_create_unavailable');
        }

        @unlink($zipPath);

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('zip_create_failed');
        }

        try {
            foreach (self::mediaDirectories() as $archiveRoot => $directory) {
                $zip->addEmptyDir($archiveRoot);

                if (is_dir($directory)) {
                    $this->addDirectoryToZip($zip, $directory, $archiveRoot);
                }
            }
        } finally {
            $closed = $zip->close();
        }

        if (! $closed || ! is_file($zipPath) || filesize($zipPath) < 1) {
            @unlink($zipPath);
            throw new RuntimeException('zip_create_failed');
        }
    }

    private function addDirectoryToZip(ZipArchive $zip, string $directory, string $archiveRoot): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = $this->relativePath($directory, $item->getPathname());
            $archivePath = $archiveRoot . '/' . str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

            if ($item->isDir()) {
                $zip->addEmptyDir($archivePath);
                continue;
            }

            if (! $this->isAllowedMediaExtension($item->getFilename())) {
                continue;
            }

            if (! $zip->addFile($item->getPathname(), $archivePath)) {
                throw new RuntimeException('zip_add_file_failed');
            }

            $zip->setCompressionName($archivePath, ZipArchive::CM_STORE);
        }
    }

    private function ensureMediaDirectoriesExist(): void
    {
        foreach (self::mediaDirectories() as $directory) {
            File::ensureDirectoryExists($directory);
        }
    }

    private function zipIncludePatterns(): array
    {
        $patterns = [];

        foreach (self::ALLOWED_MEDIA_EXTENSIONS as $extension) {
            $patterns[] = '*.' . $extension;
            $patterns[] = '*.' . strtoupper($extension);
        }

        return $patterns;
    }

    private function isAllowedMediaExtension(string $path): bool
    {
        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::ALLOWED_MEDIA_EXTENSIONS, true);
    }

    private function publicStorageDirectory(): string
    {
        return storage_path('app/public');
    }

    private function relativePath(string $baseDirectory, string $path): string
    {
        $base = rtrim($this->normalizeFilesystemPath($baseDirectory), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $target = $this->normalizeFilesystemPath($path);

        return ltrim(str_replace($base, '', $target), DIRECTORY_SEPARATOR);
    }

    private function normalizeFilesystemPath(string $path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $segments = [];

        foreach (explode(DIRECTORY_SEPARATOR, $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($segments);
                continue;
            }

            $segments[] = $segment;
        }

        $prefix = str_starts_with($path, DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '';

        return $prefix . implode(DIRECTORY_SEPARATOR, $segments);
    }

    private static function ensurePathIsInside(string $path, string $baseDirectory): void
    {
        $base = rtrim(self::normalizeStaticFilesystemPath($baseDirectory), DIRECTORY_SEPARATOR);
        $target = self::normalizeStaticFilesystemPath($path);

        if (! str_starts_with($target, $base . DIRECTORY_SEPARATOR) && $target !== $base) {
            throw new RuntimeException('backup_export_path_not_allowed');
        }
    }

    private static function normalizeStaticFilesystemPath(string $path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        $segments = [];

        foreach (explode(DIRECTORY_SEPARATOR, $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            if ($segment === '..') {
                array_pop($segments);
                continue;
            }

            $segments[] = $segment;
        }

        $prefix = str_starts_with($path, DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '';

        return $prefix . implode(DIRECTORY_SEPARATOR, $segments);
    }

    private static function findExecutable(string $name): ?string
    {
        $fileNames = PHP_OS_FAMILY === 'Windows' ? [$name . '.exe', $name] : [$name];
        $candidates = [];

        foreach (explode(PATH_SEPARATOR, getenv('PATH') ?: '') as $directory) {
            foreach ($fileNames as $fileName) {
                $candidates[] = rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
            }
        }

        if (PHP_OS_FAMILY !== 'Windows') {
            foreach (['/usr/bin', '/usr/local/bin', '/bin'] as $directory) {
                $candidates[] = $directory . DIRECTORY_SEPARATOR . $name;
            }
        }

        foreach (array_unique($candidates) as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
