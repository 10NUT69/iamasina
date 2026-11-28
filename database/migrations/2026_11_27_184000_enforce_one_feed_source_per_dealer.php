<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('dealer_feed_sources')->select('dealer_id')->groupBy('dealer_id')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Există dealeri cu mai multe surse. Alege sursa fiecărui dealer înainte de migrare.');
        }

        Schema::table('dealer_feed_sources', function (Blueprint $table) {
            $table->unique('dealer_id');
        });
    }

    public function down(): void
    {
        Schema::table('dealer_feed_sources', function (Blueprint $table) {
            $table->dropForeign(['dealer_id']);
            $table->dropUnique(['dealer_id']);
            $table->foreign('dealer_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
