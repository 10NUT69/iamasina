<?php

namespace Tests\Feature;

use App\Models\Service;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ListingFilterPerformanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createListingSchema();
        $this->seedListingData();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        Schema::dropAllTables();

        parent::tearDown();
    }

    public function test_dealer_transmission_filter_keeps_soft_deleted_services_out_and_renders_one_image_per_card(): void
    {
        $response = $this
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/anunturi-auto-de-vanzare?seller_type=dealer&cutie_viteze_id=1&ajax=1');

        $response
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('loadedCount', 2);

        $html = (string) $response->json('html');

        $this->assertStringContainsString('Dealer newest', $html);
        $this->assertStringContainsString('Dealer older', $html);
        $this->assertStringNotContainsString('Individual listing', $html);
        $this->assertStringNotContainsString('Deleted dealer listing', $html);
        $this->assertStringNotContainsString('Automatic dealer listing', $html);
        $this->assertLessThan(strpos($html, 'Dealer older'), strpos($html, 'Dealer newest'));
        $this->assertSame(2, substr_count($html, 'data-service-card'));
        $this->assertSame(2, substr_count($html, '<img src='));
        $this->assertSame(2, substr_count($html, 'x-data="listingGallery('));
        $this->assertSame(1, substr_count($html, 'loading="eager"'));
        $this->assertSame(1, substr_count($html, 'loading="lazy"'));
        $this->assertStringNotContainsString('<template x-if="isLoaded(', $html);
    }

    public function test_count_only_response_returns_filtered_total_without_card_html(): void
    {
        $response = $this
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/anunturi-auto-de-vanzare?seller_type=dealer&cutie_viteze_id=1&count_only=1');

        $response
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('facets.brands.1', 2)
            ->assertJsonPath('facets.models', [])
            ->assertJsonMissingPath('published_today')
            ->assertJsonMissingPath('html');
    }

    public function test_home_hides_generic_image_cards_without_changing_listing_or_counts(): void
    {
        DB::table('services')->where('id', 7)->update([
            'images' => json_encode([]),
        ]);

        $homeResponse = $this
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/?ajax=1');

        $homeResponse
            ->assertOk()
            ->assertJsonPath('total', 6)
            ->assertJsonPath('loadedCount', 5);

        $this->assertStringNotContainsString('Other individual listing', (string) $homeResponse->json('html'));

        $this
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/?count_only=1')
            ->assertOk()
            ->assertJsonPath('total', 6)
            ->assertJsonMissingPath('html');

        $listingResponse = $this
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/anunturi-auto-de-vanzare?ajax=1');

        $listingResponse
            ->assertOk()
            ->assertJsonPath('total', 6)
            ->assertJsonPath('loadedCount', 6);

        $this->assertStringContainsString('Other individual listing', (string) $listingResponse->json('html'));

        DB::table('services')->where('id', 7)->update([
            'images' => json_encode(['service-7-processed.webp']),
        ]);

        $processedHomeResponse = $this
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/?ajax=1');

        $processedHomeResponse
            ->assertOk()
            ->assertJsonPath('total', 6)
            ->assertJsonPath('loadedCount', 6);

        $this->assertStringContainsString('Other individual listing', (string) $processedHomeResponse->json('html'));
    }

    public function test_published_today_scope_matches_the_global_admin_definition(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 7, 16, 18, 0, 0, 'Europe/Bucharest'));

        DB::table('services')->where('id', 4)->update([
            'published_at' => '2026-07-16 09:00:00',
        ]);

        $this->assertSame(1, Service::publishedToday()->count());
    }

    public function test_filter_facets_follow_the_existing_context_and_exclude_only_their_own_dimension(): void
    {
        $dealerResponse = $this
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/anunturi-auto-de-vanzare?seller_type=dealer&count_only=1');

        $dealerResponse
            ->assertOk()
            ->assertJsonPath('total', 4)
            ->assertJsonPath('facets.brands.1', 3)
            ->assertJsonPath('facets.brands.3', 1)
            ->assertJsonPath('facets.models', []);

        $brandResponse = $this
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/anunturi-auto-de-vanzare?seller_type=dealer&brand_id=1&count_only=1');

        $brandResponse
            ->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonPath('facets.brands.1', 3)
            ->assertJsonPath('facets.brands.3', 1)
            ->assertJsonPath('facets.models.1', 3);

        $narrowedResponse = $this
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/anunturi-auto-de-vanzare?seller_type=dealer&brand_id=1&cutie_viteze_id=1&count_only=1');

        $narrowedResponse
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('facets.brands.1', 2)
            ->assertJsonPath('facets.models.1', 2);

        $this->assertNull($narrowedResponse->json('facets.brands.2'));
        $this->assertNull($narrowedResponse->json('facets.brands.3'));
    }

    public function test_brand_and_model_catalogs_include_active_listing_counts(): void
    {
        $brands = collect($this->getJson('/ajax/brands')->assertOk()->json())->keyBy('id');
        $models = collect($this->getJson('/ajax/models-by-brand?brand_id=1')->assertOk()->json())->keyBy('id');

        $this->assertSame(4, $brands->get(1)['active_services_count']);
        $this->assertSame(0, $brands->get(2)['active_services_count']);
        $this->assertSame(2, $brands->get(3)['active_services_count']);
        $this->assertSame(4, $models->get(1)['active_services_count']);
        $this->assertSame(0, $models->get(2)['active_services_count']);
    }

    public function test_price_filtering_and_sorting_use_the_normalized_eur_price(): void
    {
        DB::table('services')->where('id', 1)->update([
            'price_value' => 52584,
            'currency' => 'RON',
            'price_eur' => 10000,
        ]);

        $countResponse = $this
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/anunturi-auto-de-vanzare?seller_type=dealer&cutie_viteze_id=1&price_max=12000&count_only=1');

        $countResponse
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('facets.brands.1', 1);

        $sortResponse = $this
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->getJson('/anunturi-auto-de-vanzare?seller_type=dealer&cutie_viteze_id=1&sort=price_asc&ajax=1');

        $sortResponse->assertOk()->assertJsonPath('total', 2);

        $html = (string) $sortResponse->json('html');

        $this->assertLessThan(strpos($html, 'Dealer older'), strpos($html, 'Dealer newest'));
    }

    private function createListingSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('user_type');
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
        });

        Schema::create('counties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
        });

        Schema::create('localities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('county_id');
            $table->string('name');
            $table->string('slug');
        });

        Schema::create('combustibili', function (Blueprint $table) {
            $table->id();
            $table->string('nume');
        });

        Schema::create('cutii_viteze', function (Blueprint $table) {
            $table->id();
            $table->string('nume');
        });

        Schema::create('norme_poluare', function (Blueprint $table) {
            $table->id();
            $table->string('nume');
        });

        Schema::create('car_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_popular')->default(false);
        });

        Schema::create('car_models', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_brand_id');
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('sort_order')->default(0);
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('brand_id');
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('car_generation_id')->nullable();
            $table->unsignedBigInteger('county_id');
            $table->unsignedBigInteger('locality_id');
            $table->unsignedBigInteger('combustibil_id');
            $table->unsignedBigInteger('cutie_viteze_id');
            $table->unsignedBigInteger('norma_poluare_id')->nullable();
            $table->string('title');
            $table->string('slug');
            $table->string('city')->nullable();
            $table->unsignedSmallInteger('an_fabricatie')->nullable();
            $table->unsignedInteger('km')->nullable();
            $table->unsignedInteger('capacitate_cilindrica')->nullable();
            $table->unsignedInteger('putere')->nullable();
            $table->decimal('price_value', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->decimal('price_eur', 14, 4)->nullable();
            $table->string('price_type')->default('fixed');
            $table->json('images')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function seedListingData(): void
    {
        DB::table('users')->insert([
            ['id' => 1, 'user_type' => 'individual'],
            ['id' => 2, 'user_type' => 'dealer'],
        ]);

        DB::table('categories')->insert(['id' => 1, 'slug' => 'autoturisme']);
        DB::table('counties')->insert(['id' => 1, 'name' => 'Cluj', 'slug' => 'cluj']);
        DB::table('localities')->insert(['id' => 1, 'county_id' => 1, 'name' => 'Cluj-Napoca', 'slug' => 'cluj-napoca']);
        DB::table('combustibili')->insert(['id' => 1, 'nume' => 'Benzina']);
        DB::table('cutii_viteze')->insert([
            ['id' => 1, 'nume' => 'Manuala'],
            ['id' => 2, 'nume' => 'Automata'],
        ]);
        DB::table('norme_poluare')->insert(['id' => 1, 'nume' => 'Euro 6']);
        DB::table('car_brands')->insert([
            ['id' => 1, 'name' => 'Marca', 'slug' => 'marca'],
            ['id' => 2, 'name' => 'Fără anunțuri', 'slug' => 'fara-anunturi'],
            ['id' => 3, 'name' => 'Altă marcă', 'slug' => 'alta-marca'],
        ]);
        DB::table('car_models')->insert([
            ['id' => 1, 'car_brand_id' => 1, 'name' => 'Model', 'slug' => 'model'],
            ['id' => 2, 'car_brand_id' => 1, 'name' => 'Model fără anunțuri', 'slug' => 'model-fara-anunturi'],
            ['id' => 3, 'car_brand_id' => 3, 'name' => 'Alt model', 'slug' => 'alt-model'],
        ]);

        DB::table('services')->insert([
            $this->serviceRow(1, 2, 1, 'Dealer newest', '2026-07-12 12:00:00'),
            $this->serviceRow(2, 2, 1, 'Dealer older', '2026-07-11 12:00:00'),
            $this->serviceRow(3, 1, 1, 'Individual listing', '2026-07-13 12:00:00'),
            $this->serviceRow(4, 2, 1, 'Deleted dealer listing', '2026-07-14 12:00:00', '2026-07-14 13:00:00'),
            $this->serviceRow(5, 2, 2, 'Automatic dealer listing', '2026-07-15 12:00:00'),
            $this->serviceRow(6, 2, 2, 'Other dealer listing', '2026-07-16 12:00:00', null, 3, 3),
            $this->serviceRow(7, 1, 2, 'Other individual listing', '2026-07-17 12:00:00', null, 3, 3),
        ]);
    }

    private function serviceRow(
        int $id,
        int $userId,
        int $transmissionId,
        string $title,
        string $createdAt,
        ?string $deletedAt = null,
        int $brandId = 1,
        int $modelId = 1
    ): array {
        return [
            'id' => $id,
            'user_id' => $userId,
            'category_id' => 1,
            'brand_id' => $brandId,
            'model_id' => $modelId,
            'car_generation_id' => null,
            'county_id' => 1,
            'locality_id' => 1,
            'combustibil_id' => 1,
            'cutie_viteze_id' => $transmissionId,
            'norma_poluare_id' => 1,
            'title' => $title,
            'slug' => str($title)->slug()->toString(),
            'city' => 'Cluj-Napoca',
            'an_fabricatie' => 2020,
            'km' => 100000,
            'capacitate_cilindrica' => 1998,
            'putere' => 150,
            'price_value' => 15000,
            'currency' => 'EUR',
            'price_eur' => 15000,
            'price_type' => 'fixed',
            'images' => json_encode(["service-{$id}-1.webp", "service-{$id}-2.webp"]),
            'status' => 'active',
            'published_at' => $createdAt,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'deleted_at' => $deletedAt,
        ];
    }
}
