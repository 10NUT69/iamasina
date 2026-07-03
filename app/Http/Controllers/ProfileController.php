<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\User;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

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
        $isDealerDowngrade = $user?->user_type === 'dealer' && $request->input('user_type') === 'individual';

        if ($isDealerDowngrade && ! $request->boolean('dealer_downgrade_confirmed')) {
            return response()->json([
                'success' => false,
                'requires_dealer_downgrade_confirmation' => true,
                'message' => 'Trecerea la persoana fizica sterge datele parcului auto, imaginile, logo-ul si pagina publica.',
            ], 422);
        }

        $dealerLogoToDelete = null;
        $dealerGalleryToDelete = [];
        $userColumns = $this->userColumnAvailability([
            'phone_2',
            'phone_3',
            'dealer_description',
            'dealer_gallery',
            'dealer_logo',
            'dealer_tier',
        ]);

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
                'county'       => 'required|string|max:100',
                'city'         => 'required|string|max:100',
                'address'      => 'required|string|max:255',
            ]);

            if ($userColumns['phone_2']) {
                $rules['phone_2'] = 'nullable|string|max:30';
            }

            if ($userColumns['phone_3']) {
                $rules['phone_3'] = 'nullable|string|max:30';
            }

            if ($userColumns['dealer_description']) {
                $rules['dealer_description'] = 'nullable|string|max:3000';
            }
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
            $user->county       = $validated['county'];
            $user->city         = $validated['city'];
            $user->address      = $validated['address'];

            if ($userColumns['phone_2']) {
                $user->phone_2 = $validated['phone_2'] ?? null;
            }

            if ($userColumns['phone_3']) {
                $user->phone_3 = $validated['phone_3'] ?? null;
            }

            if ($userColumns['dealer_description']) {
                $user->dealer_description = $validated['dealer_description'] ?? null;
            }
        } else {
            // curățăm datele dacă revine la persoană fizică
            $user->company_name = null;
            $user->cui = $user->phone = $user->county = $user->city = $user->address = null;

            if ($userColumns['phone_2']) {
                $user->phone_2 = null;
            }

            if ($userColumns['phone_3']) {
                $user->phone_3 = null;
            }

            if ($userColumns['dealer_description']) {
                $user->dealer_description = null;
            }

            if ($userColumns['dealer_gallery']) {
                $dealerGalleryToDelete = array_values($user->dealer_gallery ?: []);
                $user->dealer_gallery = null;
            }

            if ($userColumns['dealer_logo']) {
                $dealerLogoToDelete = $user->dealer_logo;
                $user->dealer_logo = null;
            }

            if ($userColumns['dealer_tier']) {
                $user->dealer_tier = User::DEALER_TIER_STANDARD;
            }
        }

        // Update password ONLY if provided
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        if ($dealerLogoToDelete) {
            $this->deleteDealerLogoFile($user, $dealerLogoToDelete);
        }

        if ($dealerGalleryToDelete) {
            $this->deleteDealerGalleryFiles($user, $dealerGalleryToDelete);
        }

        return response()->json([
            'success' => true,
            'message' => 'Profil actualizat cu succes!',
        ]);
    }

    public function uploadDealerGallery(Request $request)
    {
        $user = Auth::user();

        abort_unless($user && $user->user_type === 'dealer', 403);

        if (! $this->userHasColumn('dealer_gallery')) {
            return response()->json([
                'success' => false,
                'message' => 'Galeria dealerului nu este disponibilă momentan.',
                'gallery' => [],
            ], 422);
        }

        $request->validate([
            'dealer_images' => 'required|array|max:12',
            'dealer_images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:15360',
        ], [
            'dealer_images.*.max' => 'Una dintre imagini este prea mare (max 15MB).',
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
        $canOptimizeOnServer = extension_loaded('gd');
        $manager = $canOptimizeOnServer ? new ImageManager(new Driver()) : null;
        $targetExtension = $canOptimizeOnServer ? $this->targetDealerGalleryExtension() : null;
        $baseName = $this->dealerGalleryBaseName($user);
        $nextNumber = count($gallery) + 1;
        $storedCount = 0;

        foreach (array_slice($request->file('dealer_images', []), 0, $remainingSlots) as $image) {
            if (!$image->isValid()) {
                continue;
            }

            $extension = $targetExtension ?: $this->uploadedDealerGalleryExtension($image);
            $filename = $this->availableDealerGalleryImageName($directory, $baseName, $nextNumber, $extension);
            $targetPath = $directory . DIRECTORY_SEPARATOR . $filename;

            try {
                if ($manager) {
                    $processedImage = $manager->read($image->getRealPath())->scaleDown(1600);

                    if ($extension === 'webp') {
                        $processedImage->toWebp(84)->save($targetPath);
                    } else {
                        $processedImage->toJpeg(84)->save($targetPath);
                    }
                } else {
                    $image->move($directory, $filename);
                }

                $gallery[] = 'dealers/' . $user->id . '/' . $filename;
                $storedCount++;
            } catch (Throwable $exception) {
                Log::warning('Dealer gallery image processing failed.', [
                    'user_id' => $user->id,
                    'company_name' => $user->company_name,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        if ($storedCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Nu am putut procesa imaginile încărcate.',
                'gallery' => $this->dealerGalleryPayload($user),
            ], 422);
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

        if (! $this->userHasColumn('dealer_gallery')) {
            return response()->json([
                'success' => false,
                'message' => 'Galeria dealerului nu este disponibilă momentan.',
                'gallery' => [],
            ], 422);
        }

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

        $this->deleteDealerMediaFile($user, $path);

        $user->dealer_gallery = $gallery;
        $user->save();

        return response()->json([
            'success' => true,
            'gallery' => $this->dealerGalleryPayload($user),
        ]);
    }

    public function uploadDealerLogo(Request $request)
    {
        $user = Auth::user();

        abort_unless($user && $user->user_type === 'dealer', 403);

        if (! $this->userHasColumn('dealer_logo')) {
            return response()->json([
                'success' => false,
                'message' => 'Logo-ul parcului auto nu este disponibil momentan.',
                'logo' => $this->dealerLogoPayload($user),
            ], 422);
        }

        $request->validate([
            'dealer_logo' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'dealer_logo.max' => 'Logo-ul este prea mare (max 5MB).',
        ]);

        $logo = $request->file('dealer_logo');
        if (! $logo || ! $logo->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Logo-ul incarcat nu este valid.',
                'logo' => $this->dealerLogoPayload($user),
            ], 422);
        }

        $directory = public_path('storage/dealers/' . $user->id);
        File::ensureDirectoryExists($directory);
        $canOptimizeOnServer = extension_loaded('gd');
        $manager = $canOptimizeOnServer ? new ImageManager(new Driver()) : null;
        $extension = $manager ? $this->targetDealerLogoExtension() : $this->uploadedDealerGalleryExtension($logo);
        $filename = $this->dealerLogoFileName($user, $extension);
        $targetPath = $directory . DIRECTORY_SEPARATOR . $filename;
        $oldLogo = $user->dealer_logo;

        try {
            if ($manager) {
                $processedImage = $manager->read($logo->getRealPath())->scaleDown(width: 800, height: 800);

                if ($extension === 'webp') {
                    $processedImage->toWebp(90)->save($targetPath);
                } else {
                    $processedImage->toPng()->save($targetPath);
                }
            } else {
                $logo->move($directory, $filename);
            }
        } catch (Throwable $exception) {
            Log::warning('Dealer logo processing failed.', [
                'user_id' => $user->id,
                'company_name' => $user->company_name,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Nu am putut procesa logo-ul incarcat.',
                'logo' => $this->dealerLogoPayload($user),
            ], 422);
        }

        $newLogo = 'dealers/' . $user->id . '/' . $filename;
        $user->dealer_logo = $newLogo;

        try {
            $user->save();
        } catch (Throwable $exception) {
            File::delete($targetPath);
            $user->dealer_logo = $oldLogo;

            Log::error('Dealer logo database save failed.', [
                'user_id' => $user->id,
                'company_name' => $user->company_name,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Logo-ul a fost procesat, dar nu a putut fi salvat.',
                'logo' => $this->dealerLogoPayload($user),
            ], 500);
        }

        $this->deleteDealerLogoFile($user, $oldLogo);

        return response()->json([
            'success' => true,
            'logo' => $this->dealerLogoPayload($user),
        ]);
    }

    public function deleteDealerLogo()
    {
        $user = Auth::user();

        abort_unless($user && $user->user_type === 'dealer', 403);

        if (! $this->userHasColumn('dealer_logo')) {
            return response()->json([
                'success' => false,
                'message' => 'Logo-ul parcului auto nu este disponibil momentan.',
                'logo' => null,
            ], 422);
        }

        $oldLogo = $user->dealer_logo;
        $user->dealer_logo = null;

        try {
            $user->save();
        } catch (Throwable $exception) {
            $user->dealer_logo = $oldLogo;

            Log::error('Dealer logo delete save failed.', [
                'user_id' => $user->id,
                'company_name' => $user->company_name,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Logo-ul nu a putut fi sters.',
                'logo' => $this->dealerLogoPayload($user),
            ], 500);
        }

        $this->deleteDealerLogoFile($user, $oldLogo);

        return response()->json([
            'success' => true,
            'logo' => $this->dealerLogoPayload($user),
        ]);
    }

    private function dealerGalleryPayload(User $user): array
    {
        if (! $this->userHasColumn('dealer_gallery')) {
            return [];
        }

        return collect($user->dealer_gallery ?: [])
            ->values()
            ->map(fn ($path, $index) => [
                'index' => $index,
                'path' => $path,
                'url' => asset('storage/' . ltrim($path, '/')),
            ])
            ->all();
    }

    private function dealerLogoPayload(User $user): ?array
    {
        if (! $this->userHasColumn('dealer_logo') || empty($user->dealer_logo)) {
            return null;
        }

        return [
            'path' => $user->dealer_logo,
            'url' => asset('storage/' . ltrim($user->dealer_logo, '/')),
        ];
    }

    private function targetDealerGalleryExtension(): string
    {
        return function_exists('imagewebp') ? 'webp' : 'jpg';
    }

    private function targetDealerLogoExtension(): string
    {
        return function_exists('imagewebp') ? 'webp' : 'png';
    }

    private function dealerGalleryBaseName(User $user): string
    {
        return Str::slug($user->company_name ?: $user->name ?: 'parc-auto') ?: 'parc-auto';
    }

    private function uploadedDealerGalleryExtension($image): string
    {
        $extension = strtolower($image->getClientOriginalExtension() ?: $image->guessExtension() ?: 'jpg');

        return preg_replace('/[^a-z0-9]/', '', $extension) ?: 'jpg';
    }

    private function availableDealerGalleryImageName(string $directory, string $baseName, int &$number, string $extension): string
    {
        do {
            $filename = "{$baseName}-{$number}.{$extension}";
            $number++;
        } while (is_file($directory . DIRECTORY_SEPARATOR . $filename));

        return $filename;
    }

    private function dealerLogoFileName(User $user, string $extension): string
    {
        $baseName = $this->dealerGalleryBaseName($user);
        $stamp = now()->format('YmdHis');
        $suffix = Str::lower(Str::random(6));

        return "{$baseName}-logo-{$stamp}-{$suffix}.{$extension}";
    }

    private function deleteDealerLogoFile(User $user, ?string $path): void
    {
        $this->deleteDealerMediaFile($user, $path);
    }

    private function deleteDealerGalleryFiles(User $user, array $paths): void
    {
        foreach ($paths as $path) {
            $this->deleteDealerMediaFile($user, is_string($path) ? $path : null);
        }
    }

    private function deleteDealerMediaFile(User $user, ?string $path): void
    {
        if (! $path || ! Str::startsWith($path, 'dealers/' . $user->id . '/')) {
            return;
        }

        $dealerDirectory = realpath(public_path('storage/dealers/' . $user->id));
        $targetPath = realpath(public_path('storage/' . $path));

        if (! $dealerDirectory || ! $targetPath || ! is_file($targetPath)) {
            return;
        }

        $dealerDirectory = rtrim($dealerDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (! Str::startsWith($targetPath, $dealerDirectory)) {
            return;
        }

        File::delete($targetPath);
    }

    private function userHasColumn(string $column): bool
    {
        return $this->userColumnAvailability([$column])[$column];
    }

    private function userColumnAvailability(array $columns): array
    {
        static $cache = [];

        foreach ($columns as $column) {
            $cache[$column] ??= Schema::hasColumn('users', $column);
        }

        return collect($columns)
            ->mapWithKeys(fn ($column) => [$column => $cache[$column]])
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
