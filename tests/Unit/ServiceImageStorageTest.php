<?php

namespace Tests\Unit;

use App\Support\ServiceImageStorage;
use PHPUnit\Framework\TestCase;

class ServiceImageStorageTest extends TestCase
{
    public function test_each_pending_upload_gets_a_distinct_final_filename(): void
    {
        $baseName = 'volkswagen-passat-de-vanzare-buzau-buzau';

        $first = ServiceImageStorage::processedImageFilename(
            $baseName,
            7,
            'service-image-queue/7/550e8400-e29b-41d4-a716-446655440000.jpg',
            'webp'
        );
        $second = ServiceImageStorage::processedImageFilename(
            $baseName,
            7,
            'service-image-queue/7/550e8400-e29b-41d4-a716-446655440001.jpg',
            'webp'
        );

        $this->assertSame(
            'volkswagen-passat-de-vanzare-buzau-buzau-7-550e8400e29b41d4a716446655440000.webp',
            $first
        );
        $this->assertNotSame($first, $second);
    }

    public function test_retrying_the_same_pending_upload_keeps_the_same_final_filename(): void
    {
        $pendingPath = 'service-image-queue/42/550e8400-e29b-41d4-a716-446655440000.png';

        $firstAttempt = ServiceImageStorage::processedImageFilename(
            'audi-a4-de-vanzare-cluj-cluj-napoca',
            42,
            $pendingPath,
            'webp'
        );
        $retry = ServiceImageStorage::processedImageFilename(
            'audi-a4-de-vanzare-cluj-cluj-napoca',
            42,
            $pendingPath,
            'webp'
        );

        $this->assertSame($firstAttempt, $retry);
    }

    public function test_legacy_non_uuid_pending_names_use_a_stable_fallback_token(): void
    {
        $first = ServiceImageStorage::processedImageFilename(
            'bmw-x3-de-vanzare-bucuresti-sector-1',
            99,
            'service-image-queue/99/legacy-upload.jpg',
            'webp'
        );
        $retry = ServiceImageStorage::processedImageFilename(
            'bmw-x3-de-vanzare-bucuresti-sector-1',
            99,
            'service-image-queue/99/legacy-upload.jpg',
            'webp'
        );

        $this->assertSame($first, $retry);
        $this->assertMatchesRegularExpression(
            '/^bmw-x3-de-vanzare-bucuresti-sector-1-99-[a-f0-9]{32}\.webp$/',
            $first
        );
    }
}
