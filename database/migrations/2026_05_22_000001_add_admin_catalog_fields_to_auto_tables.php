<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('car_brands', function (Blueprint $table) {
            if (!Schema::hasColumn('car_brands', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('country');
            }

            if (!Schema::hasColumn('car_brands', 'is_popular')) {
                $table->boolean('is_popular')->default(false)->after('sort_order');
            }
        });

        Schema::table('car_models', function (Blueprint $table) {
            if (!Schema::hasColumn('car_models', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('slug');
            }
        });

        $popularBrands = ['Audi', 'BMW', 'Dacia', 'Ford', 'Opel', 'Renault', 'Volkswagen', 'Mercedes-Benz', 'Skoda'];

        foreach ($popularBrands as $index => $brandName) {
            DB::table('car_brands')
                ->where('name', $brandName)
                ->update([
                    'is_popular' => true,
                    'sort_order' => ($index + 1) * 10,
                ]);
        }

        DB::table('car_models')
            ->whereNull('slug')
            ->orWhere('slug', '')
            ->orderBy('id')
            ->get(['id', 'name'])
            ->each(function ($model) {
                DB::table('car_models')
                    ->where('id', $model->id)
                    ->update(['slug' => Str::slug($model->name)]);
            });
    }

    public function down(): void
    {
        Schema::table('car_models', function (Blueprint $table) {
            if (Schema::hasColumn('car_models', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });

        Schema::table('car_brands', function (Blueprint $table) {
            if (Schema::hasColumn('car_brands', 'is_popular')) {
                $table->dropColumn('is_popular');
            }

            if (Schema::hasColumn('car_brands', 'sort_order')) {
                $table->dropColumn('sort_order');
            }
        });
    }
};
