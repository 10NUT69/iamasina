<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $ilfovId = DB::table('counties')->where('slug', 'ilfov')->value('id');

        if ($ilfovId === null) {
            return;
        }

        DB::table('localities')->updateOrInsert(
            ['siruta_code' => 179249],
            [
                'county_id' => $ilfovId,
                'name' => 'Chiajna',
                'slug' => 'chiajna',
                // Include Chiajna in the existing city pickers using iaAuto's municipality type.
                'type' => 9,
            ],
        );

        Cache::forget("iaauto:localities:county:{$ilfovId}:v1");
    }

    public function down(): void
    {
        // Keep the locality ID available to listings and dealer feeds that reference it.
    }
};
