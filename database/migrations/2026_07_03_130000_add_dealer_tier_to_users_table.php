<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STANDARD_TIER = 'standard';

    private const DEALER_TIERS = [
        'standard',
        'founding',
        'premium',
    ];

    public function up(): void
    {
        if (! Schema::hasColumn('users', 'dealer_tier')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('dealer_tier', 20)
                    ->default(self::STANDARD_TIER)
                    ->after('user_type')
                    ->index();
            });
        }

        DB::table('users')
            ->where(function ($query) {
                $query->whereNull('dealer_tier')
                    ->orWhereNotIn('dealer_tier', self::DEALER_TIERS);
            })
            ->update(['dealer_tier' => self::STANDARD_TIER]);
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'dealer_tier')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('dealer_tier');
        });
    }
};
