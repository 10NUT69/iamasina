<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminServiceController extends Controller
{
    // ==========================================================
    // 1. LISTA ANUNȚURI
    // ==========================================================
    public function index(Request $request)
    {
        // withTrashed() este obligatoriu ca să vedem și ce e în "Coș"
        $query = Service::withTrashed()->with(['user', 'category']);

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
                        $service->is_active = 0;
                        $service->status = 'pending';
                        $service->save();
                        $count++;
                    }
                    break;

                // B. ACTIVEAZĂ
                case 'activate':
                    if (!$service->trashed()) {
                        $service->is_active = 1;
                        $service->status = 'active';
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
        $service->is_active = !$service->is_active;
        $service->status = $service->is_active ? 'active' : 'pending';
        $service->save();

        return back()->with('success', 'Status actualizat.');
    }

    // ==========================================================
    // HELPER IMAGINI
    // ==========================================================
    private function deleteImages($service)
    {
        $images = $service->images;
        if (is_string($images)) $images = json_decode($images, true);
        
        if (is_array($images)) {
            foreach ($images as $img) {
                if (empty($img)) continue;
                $path = storage_path("app/public/services/" . $img);
                if (file_exists($path)) @unlink($path);
            }
        }
    }
}