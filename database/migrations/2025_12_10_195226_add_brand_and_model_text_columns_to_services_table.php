<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // pune-le unde vrei, eu le-am pus după category_id
            if (!Schema::hasColumn('services', 'brand')) {
                $table->string('brand')->nullable()->after('category_id');
            }

            if (!Schema::hasColumn('services', 'model')) {
                $table->string('model')->nullable()->after('brand');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'brand')) {
                $table->dropColumn('brand');
            }
            if (Schema::hasColumn('services', 'model')) {
                $table->dropColumn('model');
            }
        });
    }
};
