<?php

namespace Tests\Unit;

use App\Models\Service;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ServiceListingDatesTest extends TestCase
{
    public function test_original_listing_uses_published_date(): void
    {
        $service = $this->serviceWithDates(
            '2026-08-20 10:00:00',
            '2026-08-20 10:00:00'
        );

        $this->assertNull($service->renewed_at);
        $this->assertSame('2026-08-20 10:00:00', $service->listing_date->format('Y-m-d H:i:s'));
    }

    public function test_legacy_timezone_offset_is_not_treated_as_renewal(): void
    {
        $service = $this->serviceWithDates(
            '2026-07-07 20:24:31',
            '2026-07-07 23:24:31'
        );

        $this->assertNull($service->renewed_at);
        $this->assertSame('2026-07-07 20:24:31', $service->listing_date->format('Y-m-d H:i:s'));
    }

    public function test_renewed_listing_uses_renewal_date(): void
    {
        $service = $this->serviceWithDates(
            '2026-07-01 10:00:00',
            '2026-07-08 14:30:00'
        );

        $this->assertSame('2026-07-08 14:30:00', $service->renewed_at->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-08 14:30:00', $service->listing_date->format('Y-m-d H:i:s'));
    }

    public function test_same_day_renewal_is_distinct_from_legacy_timezone_offset(): void
    {
        $service = $this->serviceWithDates(
            '2026-06-13 15:47:26',
            '2026-06-13 19:37:41'
        );

        $this->assertSame('2026-06-13 19:37:41', $service->renewed_at->format('Y-m-d H:i:s'));
    }

    private function serviceWithDates(string $publishedAt, string $createdAt): Service
    {
        $service = new Service();
        $service->published_at = Carbon::parse($publishedAt, 'Europe/Bucharest');
        $service->created_at = Carbon::parse($createdAt, 'Europe/Bucharest');

        return $service;
    }
}
