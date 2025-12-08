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
       Schema::create('visits', function (Blueprint $table) {
    $table->id();
    $table->string('url');
    $table->string('ip')->nullable();
    $table->string('user_agent')->nullable();
    $table->string('country')->nullable(); // Important: API-ul poate eșua
    $table->string('city')->nullable();    // Important
    $table->string('device')->nullable();
    $table->string('browser')->nullable();
    $table->string('referer')->nullable();
    $table->unsignedBigInteger('user_id')->nullable(); // Important: Vizitatorii pot fi neautentificați
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
