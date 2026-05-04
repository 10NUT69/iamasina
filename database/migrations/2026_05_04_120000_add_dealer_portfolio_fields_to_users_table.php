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
        Schema::table('users', function (Blueprint $table) {
            $table->string('dealer_slug')->nullable()->unique()->after('company_name');
            $table->text('dealer_description')->nullable()->after('address');
            $table->json('dealer_gallery')->nullable()->after('dealer_description');
            $table->string('phone_2', 32)->nullable()->after('phone');
            $table->string('phone_3', 32)->nullable()->after('phone_2');
        });

        $this->backfillDealerSlugs();
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['dealer_slug']);
            $table->dropColumn([
                'dealer_slug',
                'dealer_description',
                'dealer_gallery',
                'phone_2',
                'phone_3',
            ]);
        });
    }

    private function backfillDealerSlugs(): void
    {
        $usedSlugs = [];

        DB::table('users')
            ->where('user_type', 'dealer')
            ->whereNotNull('company_name')
            ->where('company_name', '!=', '')
            ->orderBy('id')
            ->select(['id', 'company_name'])
            ->chunkById(100, function ($dealers) use (&$usedSlugs) {
                foreach ($dealers as $dealer) {
                    $base = Str::slug($dealer->company_name) ?: 'parc-auto';
                    $slug = $base;
                    $counter = 2;

                    while (
                        isset($usedSlugs[$slug]) ||
                        DB::table('users')
                            ->where('dealer_slug', $slug)
                            ->where('id', '!=', $dealer->id)
                            ->exists()
                    ) {
                        $slug = $base . '-' . $counter;
                        $counter++;
                    }

                    DB::table('users')
                        ->where('id', $dealer->id)
                        ->update(['dealer_slug' => $slug]);

                    $usedSlugs[$slug] = true;
                }
            });
    }
};
