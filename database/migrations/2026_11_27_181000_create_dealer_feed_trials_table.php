<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dealer_feed_trials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('dealer_feed_sources')->cascadeOnDelete();
            $table->foreignId('service_id')->unique()->constrained('services')->cascadeOnDelete();
            $table->string('external_id', 191);
            $table->char('external_key', 64);
            $table->uuid('run_id');
            $table->string('status', 20);
            $table->unsignedInteger('photo_total');
            $table->unsignedTinyInteger('photo_processed')->default(0);
            $table->json('messages')->nullable();
            $table->timestamps();
            $table->unique(['source_id', 'external_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dealer_feed_trials');
    }
};
