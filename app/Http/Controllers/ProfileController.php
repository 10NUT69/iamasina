<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * ------------------------------------------------------------
     * UPDATE PROFIL (Name, Email, Password + Tip cont + Dealer fields)
     * ------------------------------------------------------------
     */
    public function ajaxUpdate(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'name'      => 'required|string|max:100|unique:users,name,' . $user->id,
            'email'     => 'required|email|max:120|unique:users,email,' . $user->id,
            'password'  => 'nullable|min:6',
            'user_type' => 'required|in:individual,dealer',
        ];

        // 🔥 DOAR pentru parc auto
        if ($request->user_type === 'dealer') {
            $rules = array_merge($rules, [
                'company_name' => 'required|string|max:150|unique:users,company_name,' . $user->id,
                'cui'          => 'required|string|max:20',
                'phone'        => 'required|string|max:30',
                'phone_2'      => 'nullable|string|max:30',
                'phone_3'      => 'nullable|string|max:30',
                'county'       => 'required|string|max:100',
                'city'         => 'required|string|max:100',
                'address'      => 'required|string|max:255',
                'dealer_description' => 'nullable|string|max:3000',
            ]);
        }

        $validated = $request->validate($rules);

        // date de bază
        $user->name      = $validated['name'];
        $user->email     = $validated['email'];
        $user->user_type = $validated['user_type'];

        // date firmă
        if ($validated['user_type'] === 'dealer') {
            $user->company_name = $validated['company_name'];
            $user->cui          = $validated['cui'];
            $user->phone        = $validated['phone'];
            $user->phone_2      = $validated['phone_2'] ?? null;
            $user->phone_3      = $validated['phone_3'] ?? null;
            $user->county       = $validated['county'];
            $user->city         = $validated['city'];
            $user->address      = $validated['address'];
            $user->dealer_description = $validated['dealer_description'] ?? null;
        } else {
            // curățăm datele dacă revine la persoană fizică
            $user->company_name = null;
            $user->cui = $user->phone = $user->phone_2 = $user->phone_3 = $user->county = $user->city = $user->address = null;
            $user->dealer_description = null;
            $user->dealer_gallery = null;
        }

        // Update password ONLY if provided
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil actualizat cu succes!',
        ]);
    }

    public function uploadDealerGallery(Request $request)
    {
        $user = Auth::user();

        abort_unless($user && $user->user_type === 'dealer', 403);

        $request->validate([
            'dealer_images' => 'required|array|max:12',
            'dealer_images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        $gallery = array_values($user->dealer_gallery ?: []);
        $remainingSlots = max(0, 12 - count($gallery));

        if ($remainingSlots === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Galeria poate conține maximum 12 imagini.',
                'gallery' => $this->dealerGalleryPayload($user),
            ], 422);
        }

        $directory = public_path('storage/dealers/' . $user->id);
        File::ensureDirectoryExists($directory);

        foreach (array_slice($request->file('dealer_images', []), 0, $remainingSlots) as $image) {
            if (!$image->isValid()) {
                continue;
            }

            $extension = strtolower($image->getClientOriginalExtension() ?: $image->guessExtension() ?: 'jpg');
            $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: 'jpg';
            $filename = (string) Str::uuid() . '.' . $extension;

            $image->move($directory, $filename);
            $gallery[] = 'dealers/' . $user->id . '/' . $filename;
        }

        $user->dealer_gallery = array_values($gallery);
        $user->save();

        return response()->json([
            'success' => true,
            'gallery' => $this->dealerGalleryPayload($user),
        ]);
    }

    public function deleteDealerGalleryImage(int $index)
    {
        $user = Auth::user();

        abort_unless($user && $user->user_type === 'dealer', 403);

        $gallery = array_values($user->dealer_gallery ?: []);
        if (!isset($gallery[$index])) {
            return response()->json([
                'success' => false,
                'message' => 'Imaginea nu a fost găsită.',
                'gallery' => $this->dealerGalleryPayload($user),
            ], 404);
        }

        $path = $gallery[$index];
        unset($gallery[$index]);
        $gallery = array_values($gallery);

        if (Str::startsWith($path, 'dealers/' . $user->id . '/')) {
            File::delete(public_path('storage/' . $path));
        }

        $user->dealer_gallery = $gallery;
        $user->save();

        return response()->json([
            'success' => true,
            'gallery' => $this->dealerGalleryPayload($user),
        ]);
    }

    private function dealerGalleryPayload(User $user): array
    {
        return collect($user->dealer_gallery ?: [])
            ->values()
            ->map(fn ($path, $index) => [
                'index' => $index,
                'path' => $path,
                'url' => asset('storage/' . ltrim($path, '/')),
            ])
            ->all();
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
     * LIVE CHECK — Company Name (PROFILE) ✅ (parc auto)
     * ------------------------------------------------------------
     * Ruta trebuie să fie: profile.checkCompanyName
     * și URL: /profile/check-company-name
     */
    public function checkCompanyName(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:150'
        ]);

        $company = trim($request->company_name);

        // Excludem userul curent
        $exists = User::where('company_name', $company)
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
            'suggestions' => $this->generateCompanySuggestions($company),
        ]);
    }

    private function generateCompanySuggestions($company)
    {
        $base = preg_replace('/\s+/', ' ', trim($company));

        return [
            $base . ' Auto',
            $base . ' Group',
            $base . ' SRL',
            $base . ' Premium',
            $base . ' ' . rand(1, 99),
        ];
    }

    /**
     * ------------------------------------------------------------
     * LIVE CHECK — Email (PROFILE)
     * ------------------------------------------------------------
     */
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:120'
        ]);

        $email = trim($request->email);

        $exists = User::where('email', $email)
            ->where('id', '!=', Auth::id())
            ->exists();

        return response()->json([
            'available' => !$exists,
            'message'   => $exists ? 'Emailul este deja utilizat.' : 'Emailul este disponibil.'
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
