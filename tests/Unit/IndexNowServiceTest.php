<?php

namespace Tests\Unit;

use App\Services\IndexNowService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IndexNowServiceTest extends TestCase
{
    public function test_it_submits_public_urls_to_indexnow(): void
    {
        Http::fake([
            'api.indexnow.org/*' => Http::response('', 200),
        ]);

        config()->set('services.indexnow.enabled', true);
        config()->set('services.indexnow.endpoint', 'https://api.indexnow.org/indexnow');
        config()->set('services.indexnow.key', 'abc12345');
        config()->set('services.indexnow.key_location', null);

        $submitted = app(IndexNowService::class)->submit([
            'https://iaauto.ro/anunturi-auto-de-vanzare/bmw/seria-3/bucuresti/bucuresti/bmw-seria-3-123',
        ]);

        $this->assertTrue($submitted);

        Http::assertSent(function ($request): bool {
            $data = $request->data();

            return $request->url() === 'https://api.indexnow.org/indexnow'
                && $data['host'] === 'iaauto.ro'
                && $data['key'] === 'abc12345'
                && $data['keyLocation'] === 'https://iaauto.ro/abc12345.txt'
                && $data['urlList'] === [
                    'https://iaauto.ro/anunturi-auto-de-vanzare/bmw/seria-3/bucuresti/bucuresti/bmw-seria-3-123',
                ];
        });
    }

    public function test_it_does_not_submit_localhost_urls(): void
    {
        Http::fake();

        config()->set('services.indexnow.enabled', true);
        config()->set('services.indexnow.endpoint', 'https://api.indexnow.org/indexnow');
        config()->set('services.indexnow.key', 'abc12345');

        $submitted = app(IndexNowService::class)->submit('http://localhost/test');

        $this->assertFalse($submitted);

        Http::assertNothingSent();
    }
}
