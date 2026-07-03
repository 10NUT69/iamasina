<?php

namespace Tests\Unit;

use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\Caroserie;
use App\Models\Combustibil;
use App\Models\County;
use App\Models\CutieViteze;
use App\Models\Locality;
use App\Models\NormaPoluare;
use App\Models\Service;
use App\Support\ServiceShowHeading;
use PHPUnit\Framework\TestCase;

class ServiceShowHeadingTest extends TestCase
{
    public function test_builds_desktop_and_mobile_heading_variants(): void
    {
        $service = new Service([
            'an_fabricatie' => 2012,
            'capacitate_cilindrica' => 1197,
            'putere' => 105,
            'km' => 168000,
        ]);

        $service->setRelation('brandRel', new CarBrand(['name' => 'Skoda']));
        $service->setRelation('modelRel', new CarModel(['name' => 'Fabia']));
        $service->setRelation('caroserie', new Caroserie(['nume' => 'Break']));
        $service->setRelation('combustibil', new Combustibil(['nume' => 'Benzină']));
        $service->setRelation('cutieViteze', new CutieViteze(['nume' => 'Manuală']));
        $service->setRelation('normaPoluare', new NormaPoluare(['nume' => 'Euro 5']));
        $service->setRelation('locality', new Locality(['name' => 'Bacău']));
        $service->setRelation('county', new County(['name' => 'Bacău']));

        $this->assertSame(
            'Skoda Fabia Break · 2012 · Euro 5, Motorizare: 1.197 cmc, benzină, 105 CP, manuală, 168.000 km, Bacău',
            ServiceShowHeading::desktop($service)
        );

        $this->assertSame(
            'Skoda Fabia Break · 2012 · Euro 5',
            ServiceShowHeading::make($service)
        );

        $this->assertSame(
            'Skoda Fabia Break · 2012 · Euro 5',
            ServiceShowHeading::mobileTitle($service)
        );

        $this->assertSame(
            'Motorizare: 1,2 benzină · 105 CP · Manuală · 168.000 km · Bacău',
            ServiceShowHeading::mobileSpecs($service)
        );
    }

    public function test_skips_missing_fragments_without_leaving_label_text(): void
    {
        $service = new Service([
            'title' => 'Dacia Logan',
            'km' => 12000,
        ]);

        $service->setRelation('brandRel', new CarBrand(['name' => 'Dacia']));
        $service->setRelation('modelRel', new CarModel(['name' => 'Logan']));

        $this->assertSame(
            'Dacia Logan, 12.000 km',
            ServiceShowHeading::desktop($service)
        );

        $this->assertSame(
            'Dacia Logan',
            ServiceShowHeading::make($service)
        );

        $this->assertSame(
            'Dacia Logan',
            ServiceShowHeading::mobileTitle($service)
        );

        $this->assertSame(
            '12.000 km',
            ServiceShowHeading::mobileSpecs($service)
        );
    }

    public function test_keeps_different_county_in_location_label(): void
    {
        $service = new Service([
            'title' => 'Nissan Qashqai',
        ]);

        $service->setRelation('locality', new Locality(['name' => 'Râmnicu Vâlcea']));
        $service->setRelation('county', new County(['name' => 'Vâlcea']));

        $this->assertSame(
            'Nissan Qashqai, Râmnicu Vâlcea, Vâlcea',
            ServiceShowHeading::desktop($service)
        );

        $this->assertSame(
            'Râmnicu Vâlcea, Vâlcea',
            ServiceShowHeading::mobileSpecs($service)
        );
    }
}
