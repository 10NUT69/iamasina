<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Service;
use Illuminate\Support\Str;

class GenerateSlugs extends Command
{
    protected $signature = 'services:fix-slugs';
    protected $description = 'Generate missing slugs for existing services';

    public function handle()
    {
        $services = Service::whereNull('slug')->get();

        foreach ($services as $service) {
            $baseSlug = Str::slug($service->title);

            $slug = $baseSlug;
            $counter = 2;

            while (Service::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter;
                $counter++;
            }

            $service->slug = $slug;
            $service->save();
        }

        $this->info("Slugs generated successfully!");
    }
}
