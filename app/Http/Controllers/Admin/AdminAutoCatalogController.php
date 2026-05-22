<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\NormaPoluare;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminAutoCatalogController extends Controller
{
    public function index()
    {
        $brands = CarBrand::query()
            ->with([
                'models' => fn ($query) => $query
                    ->withCount('services')
                    ->ordered(),
            ])
            ->withCount(['models', 'services'])
            ->orderByDesc('is_popular')
            ->ordered()
            ->get();

        $normePoluare = NormaPoluare::query()
            ->withCount('services')
            ->ordered()
            ->get();

        return view('admin.auto-catalog.index', compact('brands', 'normePoluare'));
    }

    public function storeBrand(Request $request)
    {
        $data = $this->validateBrand($request);

        CarBrand::create($data);

        return back()->with('success', 'Marca a fost adaugata.');
    }

    public function updateBrand(Request $request, CarBrand $brand)
    {
        $data = $this->validateBrand($request, $brand);

        $brand->update($data);

        return back()->with('success', 'Marca a fost actualizata.');
    }

    public function destroyBrand(CarBrand $brand)
    {
        $usedServices = $this->brandServicesCount($brand);

        if ($usedServices > 0) {
            return back()->with('error', "Nu poti sterge marca {$brand->name}: exista {$usedServices} anunturi legate de ea.");
        }

        DB::transaction(function () use ($brand) {
            $brand->models()->delete();
            $brand->delete();
        });

        return back()->with('success', 'Marca si modelele ei nefolosite au fost sterse.');
    }

    public function storeModel(Request $request, CarBrand $brand)
    {
        $data = $this->validateModel($request, $brand);
        $data['car_brand_id'] = $brand->id;

        CarModel::create($data);

        return back()->with('success', 'Modelul a fost adaugat.');
    }

    public function updateModel(Request $request, CarModel $model)
    {
        $data = $this->validateModel($request, $model->brand, $model);

        $model->update($data);

        return back()->with('success', 'Modelul a fost actualizat.');
    }

    public function destroyModel(CarModel $model)
    {
        $usedServices = $this->modelServicesCount($model);

        if ($usedServices > 0) {
            return back()->with('error', "Nu poti sterge modelul {$model->name}: exista {$usedServices} anunturi legate de el.");
        }

        $model->delete();

        return back()->with('success', 'Modelul a fost sters.');
    }

    public function storeNorma(Request $request)
    {
        $data = $this->validateNorma($request);

        NormaPoluare::create($data);

        return back()->with('success', 'Norma de poluare a fost adaugata.');
    }

    public function updateNorma(Request $request, NormaPoluare $norma)
    {
        $data = $this->validateNorma($request, $norma);

        $norma->update($data);

        return back()->with('success', 'Norma de poluare a fost actualizata.');
    }

    public function destroyNorma(NormaPoluare $norma)
    {
        if ($norma->services()->exists()) {
            return back()->with('error', "Nu poti sterge {$norma->nume}: exista anunturi care o folosesc.");
        }

        $norma->delete();

        return back()->with('success', 'Norma de poluare a fost stearsa.');
    }

    private function validateBrand(Request $request, ?CarBrand $brand = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('car_brands', 'name')->ignore($brand?->id)],
            'slug' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_popular' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_popular'] = $request->boolean('is_popular');

        return $data;
    }

    private function validateModel(Request $request, CarBrand $brand, ?CarModel $model = null): array
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('car_models', 'name')
                    ->where('car_brand_id', $brand->id)
                    ->ignore($model?->id),
            ],
            'slug' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $data['slug'] = Str::slug($data['slug'] ?: $data['name']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    private function validateNorma(Request $request, ?NormaPoluare $norma = null): array
    {
        $data = $request->validate([
            'nume' => ['required', 'string', 'max:255', Rule::unique('norme_poluare', 'nume')->ignore($norma?->id)],
            'slug' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $data['slug'] = Str::slug($data['slug'] ?: $data['nume']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    private function brandServicesCount(CarBrand $brand): int
    {
        $modelIds = $brand->models()->pluck('id');

        return Service::query()
            ->where(function ($query) use ($brand, $modelIds) {
                $query->where('brand_id', $brand->id);

                if ($modelIds->isNotEmpty()) {
                    $query->orWhereIn('model_id', $modelIds)
                        ->orWhereHas('generation', fn ($generationQuery) => $generationQuery->whereIn('car_model_id', $modelIds));
                }
            })
            ->withTrashed()
            ->count();
    }

    private function modelServicesCount(CarModel $model): int
    {
        return Service::query()
            ->where(function ($query) use ($model) {
                $query->where('model_id', $model->id)
                    ->orWhereHas('generation', fn ($generationQuery) => $generationQuery->where('car_model_id', $model->id));
            })
            ->withTrashed()
            ->count();
    }
}
