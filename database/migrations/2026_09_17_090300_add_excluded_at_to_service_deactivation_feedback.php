<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_deactivation_feedback', function (Blueprint $table) {
            $table->timestamp('excluded_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('service_deactivation_feedback', function (Blueprint $table) {
            $table->dropColumn('excluded_at');
        });
    }
};
