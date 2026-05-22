<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('car_brands')
            ->where('name', 'Altul')
            ->update([
                'name' => 'Altă marcă',
                'sort_order' => 999,
                'is_popular' => false,
            ]);

        DB::table('car_models')
            ->whereIn('name', ['Altul', 'Altele'])
            ->update([
                'name' => 'Alt model',
                'sort_order' => 999,
            ]);
    }

    public function down(): void
    {
        DB::table('car_brands')
            ->where('name', 'Altă marcă')
            ->where('slug', 'altul')
            ->update([
                'name' => 'Altul',
                'sort_order' => 0,
            ]);

        DB::table('car_models')
            ->where('name', 'Alt model')
            ->where('slug', 'altul')
            ->update([
                'name' => 'Altul',
                'sort_order' => 0,
            ]);

        DB::table('car_models')
            ->where('name', 'Alt model')
            ->where('slug', 'altele')
            ->update([
                'name' => 'Altele',
                'sort_order' => 0,
            ]);
    }
};
