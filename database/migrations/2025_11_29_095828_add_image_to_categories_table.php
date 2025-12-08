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
        Schema::table('categories', function (Blueprint $table) {
            // Aici adăugăm coloana care va ține minte numele pozei default
            // O punem după coloana 'name' ca să fie ordonat
            // Este 'nullable' pentru că poate unele categorii nu vor avea poză la început
            $table->string('default_image')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Dacă anulăm migrarea, ștergem coloana
            $table->dropColumn('default_image');
        });
    }
};