<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request; // Necesar pentru bulkAction
use Illuminate\Support\Facades\Storage;

class AdminUserController extends Controller
{
    // ==========================================================
    // LISTA UTILIZATORILOR
    // ==========================================================
    public function index()
    {
        $users = User::withCount('services')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    // ==========================================================
    // BULK ACTIONS (ACTIVATE / DEACTIVATE / DELETE)
    // ==========================================================
    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $rawIds = $request->input('ids'); // String "1,2,3" din JS

        if (empty($rawIds)) {
            return back()->with('error', 'Selectează cel puțin un utilizator.');
        }

        $ids = explode(',', $rawIds);
        $users = User::whereIn('id', $ids)->get(); 
        $count = 0;

        foreach ($users as $user) {
            // 🛡️ PROTECȚIE: Nu te poți bloca/șterge pe tine însuți!
            if ($user->id == auth()->id()) {
                continue; 
            }

            switch ($action) {
                case 'activate':
                    $user->is_active = 1; 
                    $user->save();
                    $count++;
                    break;

                case 'deactivate':
                    $user->is_active = 0;
                    $user->save();
                    $count++;
                    break;

                case 'delete':
                    // Curățăm anunțurile și imaginile asociate
                    $this->cleanupUserResources($user);
                    $user->delete();
                    $count++;
                    break;
            }
        }

        if ($count === 0 && count($ids) > 0) {
            return back()->with('error', 'Nu poți efectua acțiuni asupra propriului cont.');
        }

        return back()->with('success', "Acțiunea '{$action}' a fost aplicată pe {$count} utilizatori.");
    }

    // ==========================================================
    // ACTIVARE / DEZACTIVARE USER
    // ==========================================================
    public function toggle($id)
    {
        $user = User::findOrFail($id);

        if ($user->id == auth()->id()) {
            return back()->with('error', 'Nu poți dezactiva propriul cont.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', 'Status utilizator actualizat.');
    }

    // ==========================================================
    // ȘTERGERE USER (Individual)
    // ==========================================================
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id == auth()->id()) {
            return back()->with('error', 'Nu poți șterge propriul cont.');
        }

        // Curățăm anunțurile și imaginile asociate
        $this->cleanupUserResources($user);

        // Ștergem utilizatorul
        $user->delete();

        return back()->with('success', 'Utilizatorul și toate anunțurile sale au fost șterse.');
    }

    // ==========================================================
    // HELPER: ȘTERGE ANUNȚURILE ȘI IMAGINILE ASOCIATE
    // (Logica preluată din metoda ta destroy)
    // ==========================================================
    private function cleanupUserResources(User $user)
    {
        $services = Service::where('user_id', $user->id)->get();

        foreach ($services as $service) {
            // Ștergere imagini
            // Asigură-te că $service->images este un array (folosește un Accessor/Mutator în model dacă e stocat ca JSON)
            if (is_string($service->images)) {
                $images = json_decode($service->images, true);
            } else {
                $images = $service->images;
            }

            if (is_array($images)) {
                foreach ($images as $img) {
                    if (!empty($img) && Storage::exists($img)) {
                        Storage::delete($img);
                    }
                }
            }

            // Ștergem înregistrarea serviciului
            $service->delete();
        }
    }
}