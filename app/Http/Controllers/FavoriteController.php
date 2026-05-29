<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Service;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Toggle favorite (adaugă / scoate).
     * Răspunde cu JSON pentru a fi folosit de AJAX.
     */
    public function toggle(Request $request)
    {
        // 1. Verificare autentificare
        if (!auth()->check()) {
            return response()->json(['status' => 'guest', 'message' => 'Trebuie să fii autentificat.'], 401);
        }

        // 2. Validare input
        $request->validate([
            'service_id' => 'required|exists:services,id',
        ]);

        $serviceId = $request->service_id;
        $userId = auth()->id();

        // 3. Căutăm dacă există deja în favorite
        $fav = Favorite::where('user_id', $userId)
                       ->where('service_id', $serviceId)
                       ->first();

        // 4. Logică Toggle
        if ($fav) {
            // Dacă există -> Ștergem (Unfavorite)
            $fav->delete();
            return response()->json(['status' => 'removed']);
        } else {
            // Dacă nu există -> Creăm (Favorite)
            Favorite::create([
                'user_id'    => $userId,
                'service_id' => $serviceId,
            ]);
            return response()->json(['status' => 'added']);
        }
    }

    public function import(Request $request)
    {
        $data = $request->validate([
            'service_ids' => ['required', 'array', 'max:100'],
            'service_ids.*' => ['integer'],
        ]);

        $serviceIds = collect($data['service_ids'])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($serviceIds->isEmpty()) {
            return response()->json(['status' => 'empty', 'imported' => 0]);
        }

        $validServiceIds = Service::query()
            ->whereIn('id', $serviceIds)
            ->where('status', 'active')
            ->pluck('id');

        $imported = 0;
        foreach ($validServiceIds as $serviceId) {
            $favorite = Favorite::firstOrCreate([
                'user_id' => $request->user()->id,
                'service_id' => $serviceId,
            ]);

            if ($favorite->wasRecentlyCreated) {
                $imported++;
            }
        }

        return response()->json([
            'status' => 'ok',
            'imported' => $imported,
        ]);
    }
}
