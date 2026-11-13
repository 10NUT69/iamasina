<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ContractAutoController extends Controller
{
    private const MODEL_YEAR = 2026;

    private const META_DESCRIPTION = 'Completează online gratuit contract de vânzare cumpărare Model 2026 ITL 054. Descarcă Contractul auto format PDF sau Word Model Oficial.';

    private const DOWNLOAD_TYPES = [
        'pdf' => 'application/pdf',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    public function index(): View
    {
        $years = $this->displayYears();

        return view('services.contract-auto.index', [
            'contractTitle' => $this->title($years),
            'contractMetaTitle' => "Contract vânzare cumpărare auto {$years} gratuit | iaAuto.ro",
            'contractMetaDescription' => self::META_DESCRIPTION,
        ]);
    }

    public function download(string $format): BinaryFileResponse
    {
        abort_unless(isset(self::DOWNLOAD_TYPES[$format]), 404);

        $asset = 'js/contract-auto/assets/itl-054-'.self::MODEL_YEAR.'.'.$format;

        return response()->download(resource_path($asset), $this->title($this->displayYears()).'.'.$format, [
            'Content-Type' => self::DOWNLOAD_TYPES[$format],
        ]);
    }

    private function displayYears(): string
    {
        $currentYear = now()->year;

        return $currentYear > self::MODEL_YEAR
            ? self::MODEL_YEAR.'-'.$currentYear
            : (string) self::MODEL_YEAR;
    }

    private function title(string $years): string
    {
        return "Contract Vânzare Cumpărare Auto {$years} – Model ".self::MODEL_YEAR.' ITL 054';
    }
}
