<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceImageStorage
{
    public const SERVICE_DIR = 'services';
    public const CARD_THUMBNAIL_DIR = 'services/thumbnails/card';

    public static function cardImageUrl(mixed $image): ?string
    {
        $filename = self::localServiceFilename($image);

        if ($filename && Storage::disk('public')->exists(self::CARD_THUMBNAIL_DIR . '/' . $filename)) {
            return asset('storage/' . self::CARD_THUMBNAIL_DIR . '/' . $filename);
        }

        return self::publicImageUrl($image);
    }

    public static function publicImageUrl(mixed $image): ?string
    {
        $path = self::imagePath($image);

        if ($path === null || $path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, ['storage/', 'images/'])) {
            return asset($path);
        }

        if (Str::startsWith($path, self::SERVICE_DIR . '/')) {
            return asset('storage/' . $path);
        }

        return asset('storage/' . self::SERVICE_DIR . '/' . $path);
    }

    public static function deleteImageFiles(mixed $image): void
    {
        $filename = self::localServiceFilename($image);

        if (!$filename) {
            return;
        }

        Storage::disk('public')->delete([
            self::SERVICE_DIR . '/' . $filename,
            self::CARD_THUMBNAIL_DIR . '/' . $filename,
        ]);
    }

    public static function deleteServiceImages(mixed $images): void
    {
        if (is_object($images) && method_exists($images, 'all')) {
            $images = $images->all();
        }

        if (is_string($images)) {
            $images = json_decode($images, true) ?: [];
        }

        if (!is_array($images)) {
            return;
        }

        foreach ($images as $image) {
            self::deleteImageFiles($image);
        }
    }

    public static function localServiceFilename(mixed $image): ?string
    {
        $path = self::imagePath($image);

        if ($path === null || $path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', 'images/'])) {
            return null;
        }

        foreach ([
            'storage/' . self::CARD_THUMBNAIL_DIR . '/',
            self::CARD_THUMBNAIL_DIR . '/',
            'storage/' . self::SERVICE_DIR . '/',
            self::SERVICE_DIR . '/',
        ] as $prefix) {
            if (Str::startsWith($path, $prefix)) {
                $path = Str::after($path, $prefix);
                break;
            }
        }

        if ($path === '' || str_contains($path, '/') || str_contains($path, '\\') || str_contains($path, '..')) {
            return null;
        }

        return basename($path);
    }

    private static function imagePath(mixed $image): ?string
    {
        if (is_array($image)) {
            $image = $image['path'] ?? $image['url'] ?? null;
        } elseif (is_object($image)) {
            $image = $image->path ?? $image->url ?? null;
        }

        if (!is_string($image)) {
            return null;
        }

        return ltrim(trim($image), '/');
    }
}
