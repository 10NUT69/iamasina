<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Services\IndexNowService;
use App\Support\ServiceImageStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminServiceController extends Controller
{
    // ==========================================================
    // 1. LISTA ANUNȚURI
    // ==========================================================
    public function index(Request $request)
    {
        // withTrashed() este obligatoriu ca să vedem și ce e în "Coș"
        $query = Service::withTrashed()->with([
            'user',
            'category',
            'county',
            'locality',
            'brandRel',
            'modelRel',
            'generation.model.brand',
        ]);

        // Căutare
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('title', 'like', "%$s%")
                  ->orWhere('id', $s)
                  ->orWhereHas('user', function($u) use ($s) {
                      $u->where('email', 'like', "%$s%");
                  });
            });
        }

        // Filtrare Status
        if ($request->filled('status')) {
            if ($request->status === 'trashed') {
                $query->onlyTrashed();
            } elseif ($request->status === 'active') {
                $query->where('status', 'active')->whereNull('deleted_at');
            } elseif ($request->status === 'inactive') {
                $query->where('status', '!=', 'active')->whereNull('deleted_at');
            }
        }

        $services = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('admin.services.index', compact('services'));
    }

    public function submitIndexNow(IndexNowService $indexNow)
    {
        $submitted = 0;
        $failedBatches = 0;

        $total = Service::query()
            ->where('status', 'active')
            ->withoutTrashed()
            ->count();

        if ($total === 0) {
            return back()->with('error', 'IndexNow: nu există anunțuri active de trimis.');
        }

        Service::query()
            ->with([
                'county',
                'locality.county',
                'brandRel',
                'modelRel',
                'generation.model.brand',
            ])
            ->where('status', 'active')
            ->withoutTrashed()
            ->orderBy('id')
            ->chunkById(10000, function ($services) use ($indexNow, &$submitted, &$failedBatches) {
                $urls = $services
                    ->map(fn (Service $service) => $service->public_url)
                    ->filter(fn (string $url) => $url !== url('/'))
                    ->values()
                    ->all();

                if ($urls === []) {
                    return;
                }

                if ($indexNow->submit($urls)) {
                    $submitted += count($urls);
                    return;
                }

                $failedBatches++;
            });

        if ($submitted === 0) {
            return back()->with('error', 'IndexNow: nu am putut trimite URL-urile. Verifică APP_URL și fișierul cheie.');
        }

        if ($failedBatches > 0) {
            return back()->with('error', "IndexNow: am trimis {$submitted} URL-uri, dar {$failedBatches} loturi au eșuat.");
        }

        return back()->with('success', "IndexNow: am trimis {$submitted} URL-uri active.");
    }

    // ==========================================================
    // 1B. EDIT FORM (ADMIN)
    // ==========================================================
    public function edit($id)
    {
        $service = Service::withTrashed()
            ->with([
                'category',
                'county',
                'locality',
                'user',
                'brandRel',
                'modelRel',
                'generation.model.brand',
                'combustibil',
                'cutieViteze',
                'caroserie',
                'culoare',
                'tractiune',
                'normaPoluare',
                'culoareOpt',
            ])
            ->findOrFail($id);

        $categories = Category::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.services.edit', compact('service', 'categories'));
    }

    // ==========================================================
    // 1C. UPDATE (ADMIN)
    // ==========================================================
    public function update(Request $request, $id)
    {
        $service = Service::withTrashed()->findOrFail($id);

        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'category_id' => ['required', 'exists:categories,id'],
            'price_value' => ['nullable', 'numeric'],
            'price_type'  => ['required', 'in:fixed,negotiable'],
            'currency'    => ['required', 'in:RON,EUR'],
            'phone'       => ['nullable', 'string', 'max:30'],
            'email'       => ['nullable', 'email', 'max:120'],
            'status'      => ['required', 'in:active,pending,expired,rejected'],
        ]);

        $service->title       = $data['title'];
        $service->description = $data['description'];
        $service->category_id = $data['category_id'];
        $service->price_value = $data['price_value'];
        $service->price_type  = $data['price_type'];
        $service->currency    = $data['currency'];
        $service->phone       = $data['phone'];
        $service->email       = $data['email'];
        $service->status      = $data['status'];
        if (Schema::hasColumn('services', 'is_active')) {
            $service->is_active = $data['status'] === 'active';
        }
        $service->save();

        return back()->with('success', 'Anuntul a fost actualizat.');
    }

    // ==========================================================
    // 1D. DELETE IMAGE (ADMIN)
    // ==========================================================
    public function deleteImage(Request $request, $id)
    {
        $service = Service::withTrashed()->findOrFail($id);

        $request->validate([
            'image' => ['required', 'string'],
        ]);

        $imageName = $request->input('image');
        $images = $service->images;

        if (is_string($images)) {
            $images = json_decode($images, true);
        }

        if (!is_array($images)) {
            $images = [];
        }

        if (!in_array($imageName, $images, true)) {
            return back()->with('error', 'Imaginea nu a fost găsită în acest anunț.');
        }

        $images = array_values(array_filter($images, fn ($image) => $image !== $imageName));

        ServiceImageStorage::deleteImageFiles($imageName);

        $service->images = count($images) ? $images : null;
        $service->save();

        return back()->with('success', 'Imagine stearsa.');
    }

    // ==========================================================
    // 2. BULK ACTIONS (LOGICA PRINCIPALĂ CERUTĂ)
    // ==========================================================
    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $rawIds = $request->input('ids'); // Vine ca string "1,2,5" din JS

        if (empty($rawIds)) {
            return back()->with('error', 'Nu ai selectat nimic.');
        }

        $ids = explode(',', $rawIds);

        // Luăm TOATE serviciile (inclusiv cele șterse)
        $services = Service::withTrashed()->whereIn('id', $ids)->get();
        $count = 0;

        foreach ($services as $service) {
            switch ($action) {
                
                // A. DEZACTIVEAZĂ (Doar status)
                case 'deactivate':
                    if (!$service->trashed()) {
                        $service->status = 'pending';
                        if (Schema::hasColumn('services', 'is_active')) {
                            $service->is_active = 0;
                        }
                        $service->save();
                        $count++;
                    }
                    break;

                // B. ACTIVEAZĂ
                case 'activate':
                    if (!$service->trashed()) {
                        $service->status = 'active';
                        if (Schema::hasColumn('services', 'is_active')) {
                            $service->is_active = 1;
                        }
                        $service->save();
                        $count++;
                    }
                    break;

                // C. SOFT DELETE (Mută în Coș + Șterge Poze)
                case 'soft_delete':
                    if (!$service->trashed()) {
                        $this->deleteImages($service); // Ștergem pozele fizic
                        $service->images = null;       // Golim coloana images
                        $service->save();
                        $service->delete();            // Soft Delete (deleted_at)
                        $count++;
                    }
                    break;

                // D. FORCE DELETE (Șterge Definitiv)
                case 'force_delete':
                    $this->deleteImages($service);     // Ștergem pozele (safety check)
                    $service->forceDelete();           // Ștergem rândul din SQL
                    $count++;
                    break;
            }
        }

        return back()->with('success', "Acțiunea '{$action}' a fost aplicată pe {$count} anunțuri.");
    }

    // ==========================================================
    // 3. ȘTERGERE INDIVIDUALĂ (Butoanele de pe rând)
    // ==========================================================
    public function destroy(Request $request, $id)
    {
        $service = Service::withTrashed()->findOrFail($id);
        
        // Verificăm dacă s-a cerut explicit Force Delete (din butonul roșu plin)
        $force = $request->has('force') && $request->force == '1';

        // CAZ: FORCE DELETE (Definitiv)
        if ($force || $service->trashed()) {
            $this->deleteImages($service);
            $service->forceDelete();
            return back()->with('success', 'Anunț șters DEFINITIV.');
        }

        // CAZ: SOFT DELETE (Coș)
        $this->deleteImages($service);
        $service->images = null;
        $service->save();
        $service->delete();

        return back()->with('success', 'Anunț mutat în coș.');
    }

    // ==========================================================
    // 4. TOGGLE INDIVIDUAL
    // ==========================================================
    public function toggle($id)
    {
        $service = Service::findOrFail($id);
        $isActive = $service->status === 'active';
        $service->status = $isActive ? 'pending' : 'active';

        if (Schema::hasColumn('services', 'is_active')) {
            $service->is_active = !$isActive;
        }

        $service->save();

        return back()->with('success', 'Status actualizat.');
    }

    // ==========================================================
    // HELPER IMAGINI
    // ==========================================================
    private function deleteImages($service)
    {
        ServiceImageStorage::deleteServiceImages($service->images);
    }
}
