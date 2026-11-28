<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dealer_feed_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dealer_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 120);
            $table->text('url');
            $table->json('mapping')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->json('last_check')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dealer_feed_sources');
    }
};
