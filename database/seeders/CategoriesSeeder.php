<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            "Zugrav",
            "Electrician",
            "Instalator",
            "Dulgher",
            "Muncitor construcții",
            "Fierar betonist",
            "Gletuitor / Finisor",
            "Faianțar / Gresie",
            "Parchetar",
            "Acoperișuri",
            "Montator uși / ferestre",
            "Mobilă la comandă",
            "Curățenie",
            "Servicii auto",
            "Frigotehnist",
            "Termopane",
            "Centrală termică",
            "Amenajări interioare",
            "Tencuieli / glet / finisaje",
            "Peisagistică / grădinărit",
            "Altele"
        ];

        foreach ($categories as $cat) {
            DB::table('categories')->insert([
                'name' => $cat,
                'slug' => Str::slug($cat),
                'icon' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
