<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('services', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        $table->foreignId('category_id')->constrained()->cascadeOnDelete();
        $table->foreignId('county_id')->constrained()->cascadeOnDelete();

        $table->string('title');
        $table->text('description');

        $table->string('city')->nullable();

        $table->string('phone')->nullable();
        $table->string('email')->nullable();

        $table->json('images')->nullable();

        $table->enum('status', ['active', 'pending', 'expired', 'rejected'])->default('pending');

        $table->dateTime('published_at')->nullable();
        $table->dateTime('expires_at')->nullable();

        $table->unsignedBigInteger('views')->default(0);

        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('services');
}
};
