<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Throwable;

class DealerFeedPhotoDownload
{
    public function __construct(private DealerFeedDownload $download) {}

    public function collect(array $photos, string $domain, string $directory): array
    {
        $paths = [];
        $messages = [];
        $deadline = microtime(true) + 65;
        $extensions = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
        foreach ($photos as $index => $url) {
            $position = $index + 1;
            $seconds = min(5, (int) ($deadline - microtime(true)));
            if ($seconds < 1) {
                $messages[] = 'Descărcarea s-a oprit la poza '.$position.' deoarece a expirat timpul alocat.';
                break;
            }

            try {
                $bytes = $this->download->fetchPhoto($url, $domain, $seconds);
                $image = @getimagesizefromstring($bytes);
                $extension = $extensions[$image[2] ?? 0] ?? null;
                if (! $extension || $image[0] * $image[1] > 40000000) {
                    throw new DealerFeedException('Fișierul trebuie să fie JPG, PNG sau WebP și să aibă maximum 40 megapixeli.');
                }
                $path = $directory.'/'.$position.'.'.$extension;
                if (! Storage::put($path, $bytes)) {
                    throw new DealerFeedException('Fișierul nu a putut fi stocat temporar.');
                }
                $paths[$position] = $path;
            } catch (DealerFeedException $exception) {
                $messages[] = 'Poza '.$position.': '.$exception->getMessage();
            } catch (Throwable) {
                $messages[] = 'Poza '.$position.': descărcarea a eșuat.';
            }
        }

        return [$paths, $messages];
    }
}
