<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
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
}