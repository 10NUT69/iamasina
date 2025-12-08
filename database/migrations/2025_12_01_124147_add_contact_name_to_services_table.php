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
        Schema::table('services', function (Blueprint $table) {
            // Adăugăm coloana contact_name după user_id
            // O facem nullable pentru că userii înregistrați nu au nevoie de ea
            $table->string('contact_name')->nullable()->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Ștergem coloana dacă dăm rollback la migrare
            $table->dropColumn('contact_name');
        });
    }
};