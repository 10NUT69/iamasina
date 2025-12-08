<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * ------------------------------------------------------------
     * UPDATE PROFIL (Name, Email, Password)
     * ------------------------------------------------------------
     */
    public function ajaxUpdate(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'     => 'required|string|max:100|unique:users,name,' . $user->id,
            'email'    => 'required|email|max:120|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6'
        ]);

        // Update name + email
        $user->name  = $request->name;
        $user->email = $request->email;

        // Update password ONLY if provided
        if (!empty($request->password)) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'status'  => 'profile-updated',
            'message' => 'Profil actualizat cu succes!'
        ]);
    }


    /**
     * ------------------------------------------------------------
     * LIVE CHECK — Name (PROFILE)
     * ------------------------------------------------------------
     */
    public function checkName(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100'
        ]);

        $name = trim($request->name);

        $exists = User::where('name', $name)
            ->where('id', '!=', Auth::id())
            ->exists();

        if (!$exists) {
            return response()->json([
                'available'   => true,
                'suggestions' => []
            ]);
        }

        return response()->json([
            'available'   => false,
            'suggestions' => $this->generateSuggestions($name)
        ]);
    }


    /**
     * ------------------------------------------------------------
     * LIVE CHECK — Name (REGISTER)
     * ------------------------------------------------------------
     */
    public function checkNameRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100'
        ]);

        $name = trim($request->name);

        $exists = User::where('name', $name)->exists();

        if (!$exists) {
            return response()->json([
                'available'   => true,
                'suggestions' => []
            ]);
        }

        return response()->json([
            'available'   => false,
            'suggestions' => $this->generateSuggestions($name)
        ]);
    }


    /**
     * ------------------------------------------------------------
     * LIVE CHECK — Email (REGISTER)
     * ------------------------------------------------------------
     */
    public function checkEmailRegister(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:120'
        ]);

        $email = trim($request->email);

        $exists = User::where('email', $email)->exists();

        if ($exists) {
            return response()->json([
                'available' => false,
                'message'   => 'Emailul este deja utilizat.'
            ]);
        }

        return response()->json([
            'available' => true,
            'message'   => 'Emailul este disponibil.'
        ]);
    }


    /**
     * ------------------------------------------------------------
     * PRIVATE — Generate username suggestions
     * ------------------------------------------------------------
     */
    private function generateSuggestions($name)
    {
        return [
            $name . rand(1, 99),
            $name . '_' . rand(100, 999),
            $name . date('Y'),
            strtolower($name) . '_official',
            'real_' . strtolower($name),
        ];
    }
}
