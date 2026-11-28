<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dealer_feed_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('dealer_feed_sources')->cascadeOnDelete();
            $table->string('profile', 40);
            $table->string('status', 20);
            $table->string('snapshot_path')->nullable();
            $table->json('result')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dealer_feed_runs');
    }
};
