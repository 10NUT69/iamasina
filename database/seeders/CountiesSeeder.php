<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CountiesSeeder extends Seeder
{
    public function run(): void
    {
        $counties = [
            "Alba",
            "Arad",
            "Argeș",
            "Bacău",
            "Bihor",
            "Bistrița-Năsăud",
            "Botoșani",
            "Brașov",
            "Brăila",
            "București",
            "Buzău",
            "Caraș-Severin",
            "Călărași",
            "Cluj",
            "Constanța",
            "Covasna",
            "Dâmbovița",
            "Dolj",
            "Galați",
            "Giurgiu",
            "Gorj",
            "Harghita",
            "Hunedoara",
            "Ialomița",
            "Iași",
            "Ilfov",
            "Maramureș",
            "Mehedinți",
            "Mureș",
            "Neamț",
            "Olt",
            "Prahova",
            "Satu Mare",
            "Sălaj",
            "Sibiu",
            "Suceava",
            "Teleorman",
            "Timiș",
            "Tulcea",
            "Vaslui",
            "Vâlcea",
            "Vrancea"
        ];

        foreach ($counties as $county) {
            DB::table('counties')->insert([
                'name' => $county,
                'slug' => Str::slug($county),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}