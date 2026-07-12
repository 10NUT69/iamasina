<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
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
        });

        Schema::create('car_models', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('car_brand_id');
            $table->string('name');
            $table->string('slug');
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
        DB::table('car_brands')->insert(['id' => 1, 'name' => 'Marca', 'slug' => 'marca']);
        DB::table('car_models')->insert(['id' => 1, 'car_brand_id' => 1, 'name' => 'Model', 'slug' => 'model']);

        DB::table('services')->insert([
            $this->serviceRow(1, 2, 1, 'Dealer newest', '2026-07-12 12:00:00'),
            $this->serviceRow(2, 2, 1, 'Dealer older', '2026-07-11 12:00:00'),
            $this->serviceRow(3, 1, 1, 'Individual listing', '2026-07-13 12:00:00'),
            $this->serviceRow(4, 2, 1, 'Deleted dealer listing', '2026-07-14 12:00:00', '2026-07-14 13:00:00'),
            $this->serviceRow(5, 2, 2, 'Automatic dealer listing', '2026-07-15 12:00:00'),
        ]);
    }

    private function serviceRow(
        int $id,
        int $userId,
        int $transmissionId,
        string $title,
        string $createdAt,
        ?string $deletedAt = null
    ): array {
        return [
            'id' => $id,
            'user_id' => $userId,
            'category_id' => 1,
            'brand_id' => 1,
            'model_id' => 1,
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
