<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Category;
use App\Models\County;
use App\Models\User;

// 🔹 MODELE AUTO
use App\Models\CarBrand; // Am corectat numele clasei (Brand -> CarBrand conform modelelor tale)
use App\Models\CarModel;
use App\Models\CarGeneration; // Asigură-te că ai creat acest model la pasul anterior

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    // ==========================================
    // 1. INDEX (NESCHIMBAT)
    // ==========================================
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPageFirst = 10;
        $perPageNext  = 8;

        if ($page == 1) {
            $limit  = $perPageFirst;
            $offset = 0;
        } else {
            $limit  = $perPageNext;
            $offset = $perPageFirst + (($page - 2) * $perPageNext);
        }

        $query = Service::where('status', 'active');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                    ->orWhere('description', 'like', "%$search%");
            });
        }

        if ($request->filled('county')) {
            $query->where('county_id', $request->county);
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $totalCount = $query->count();

        $services = $query
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $loadedSoFar = $offset + $services->count();
        $hasMore     = $loadedSoFar < $totalCount;

        if ($request->ajax()) {
            $html = view('services.partials.service_cards', ['services' => $services])->render();

            return response()->json([
                'html'        => $html,
                'hasMore'     => $hasMore,
                'total'       => $totalCount,
                'loadedCount' => $services->count(),
            ]);
        }

        return view('services.index', [
            'services'        => $services,
            'counties'        => County::all(),
            'categories'      => Category::orderBy('sort_order', 'asc')->get(),
            'hasMore'         => $hasMore,
            'currentCategory' => $request->attributes->get('currentCategory'),
            'currentCounty'   => $request->attributes->get('currentCounty'),
        ]);
    }

    // ==========================================
    // 2. INDEX LOCATION (NESCHIMBAT)
    // ==========================================
    public function indexLocation(Request $request, $categorySlug, $countySlug = null)
    {
        $category = Category::where('slug', $categorySlug)->firstOrFail();
        $county = null;
        if ($countySlug) {
            $county = County::where('slug', $countySlug)->firstOrFail();
        }

        $request->merge([
            'category' => $category->id,
            'county'   => $county ? $county->id : null,
        ]);

        $request->attributes->set('currentCategory', $category);
        if ($county) {
            $request->attributes->set('currentCounty', $county);
        }

        return $this->index($request);
    }

    // ==========================================
    // 3. SHOW (NESCHIMBAT)
    // ==========================================
    public function show($category, $county, $slug, $id)
    {
        $service = Service::withTrashed()->with(['category', 'county', 'user'])->findOrFail($id);

        $correctSlug = $service->smart_slug;
        if ($slug !== $correctSlug) {
            return redirect()->to($service->public_url, 301);
        }

        if (!$service->trashed()) {
            $service->increment('views');
        }

        return view('services.show', compact('service'));
    }

    // ==========================================
    // 4. CREATE (ACTUALIZAT COMPLET)
    // ==========================================
    public function create()
    {
        // 1. Luăm brandurile
        $brands = CarBrand::orderBy('name')->get();

        // 2. Luăm Modelele + Generațiile (Eager Loading)
        // Asta rezolvă problema cu Generațiile lipsă
        $models = CarModel::with(['generations' => function($q) {
            $q->orderBy('year_start', 'asc');
        }])->get();

        // 3. Construim structura JSON pentru JavaScript
        $carData = [];

        foreach ($models as $model) {
            // Sărim peste modelele fără brand
            if (!$model->brand) continue;

            $brandName = $model->brand->name;
            $modelName = $model->name;

            // Dacă modelul are generații, le adăugăm
            if ($model->generations->isNotEmpty()) {
                foreach ($model->generations as $gen) {
                    $carData[$brandName][$modelName][] = [
                        'name'  => $gen->name,       // ex: "V"
                        'start' => $gen->year_start, // ex: 2004
                        'end'   => $gen->year_end    // ex: 2008 sau NULL
                    ];
                }
            } else {
                // Dacă nu are generații, inițializăm un array gol ca să știe JS-ul că există modelul
                if (!isset($carData[$brandName][$modelName])) {
                    $carData[$brandName][$modelName] = [];
                }
            }
        }

        return view('services.create', [
            'categories' => Category::orderBy('sort_order', 'asc')->get(),
            'counties'   => County::all(),
            'brands'     => $brands,
            'carData'    => $carData, // Trimitem structura complexă către View
        ]);
    }

    // ==========================================
    // 5. STORE (ACTUALIZAT VALIDARE)
    // ==========================================
    public function store(Request $request)
    {
        $rules = [
            'title'       => 'required|max:255',
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'county_id'   => 'required|exists:counties,id',
            'phone'       => 'required|string|max:30',
            'price_value' => 'nullable|numeric',
            'price_type'  => 'required|in:fixed,negotiable',
            'currency'    => 'required|in:RON,EUR',
            'name'        => 'nullable|string|max:255',
            'images.*'    => 'image|mimes:jpeg,png,jpg,webp|max:15360',

            // 🔹 CÂMPURI AUTO NOI (Texte venite din Select-uri)
            'brand'               => 'nullable|string|max:100',
            'model'               => 'nullable|string|max:100',
            'generation'          => 'nullable|string|max:100', // NOU
            'year'                => 'nullable|integer|min:1950|max:' . (date('Y') + 1), // "year" vine din form, nu "year_of_fabrication"
            'fuel_type'           => 'nullable|string|max:50',
            'transmission'        => 'nullable|string|max:50',
            'body_type'           => 'nullable|string|max:100',
            'power'               => 'nullable|integer',
            'engine_size'         => 'nullable|integer',
            'vin'                 => 'nullable|string|max:50',
            'color'               => 'nullable|string|max:50',
            'pollution_standard'  => 'nullable|string|max:50',
            'mileage'             => 'nullable|numeric',
        ];

        if (!Auth::check() && $request->filled('email') && $request->filled('password')) {
            $rules['email']    = 'required|email|unique:users,email|max:120';
            $rules['password'] = 'required|string|min:6';
        }

        $messages = [
            'images.*.max'      => 'Una dintre imagini este prea mare (max 15MB).',
            'images.*.uploaded' => 'Eroare la încărcare server.',
        ];

        $validated = $request->validate($rules, $messages);

        // 1. CALCULARE NUME UTILIZATOR (VISITOR)
        $calculatedName = $request->input('name');
        if (empty($calculatedName) && $request->filled('email')) {
            $emailParts = explode('@', $request->input('email'));
            $rawName    = $emailParts[0];
            $nameParts  = preg_split('/[\.\_\-\d]/', $rawName);
            if (!empty($nameParts[0])) {
                $calculatedName = ucfirst($nameParts[0]);
            } else {
                $calculatedName = ucfirst(preg_replace('/[^A-Za-z0-9]/', '', $rawName));
            }
        }
        if (empty($calculatedName)) {
            $calculatedName = 'Vizitator';
        }

        // 2. LOGICA USER
        $userId = null;
        if (Auth::check()) {
            $userId = Auth::id();
        } elseif ($request->filled('email') && $request->filled('password')) {
            $user = User::create([
                'name'     => $calculatedName,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);
            Auth::login($user);
            $userId = $user->id;
        }

        // 3. SALVARE SERVICIU
        $service            = new Service();
        $service->user_id   = $userId;
        if (!$userId) {
            $service->contact_name = $calculatedName;
        }

        // Date standard
        $service->title       = $validated['title'];
        $service->description = $validated['description'];
        $service->category_id = $validated['category_id'];
        $service->county_id   = $validated['county_id'];
        $service->phone       = $validated['phone'];
        $service->price_value = $request->price_value;
        $service->price_type  = $validated['price_type'];
        $service->currency    = $validated['currency'];

        if ($request->filled('email')) {
            $service->email = $request->email;
        }

        // 🔹 Mapare date AUTO (Form -> DB Columns)
        // Asigură-te că ai aceste coloane în tabelul services sau folosești un tabel separat
        $service->brand         = $request->input('brand');
        $service->model         = $request->input('model');
        // Dacă nu ai coloana generation, poți să o concatenezi la model sau descriere
        // $service->generation = $request->input('generation'); 
        
        $service->year_of_fabrication = $request->input('year'); // Formularul trimite "year", DB are "year_of_fabrication"
        $service->mileage       = $request->input('mileage');
        $service->fuel_type     = $request->input('fuel_type');
        $service->gearbox       = $request->input('transmission'); // Form "transmission" -> DB "gearbox"
        $service->body_type     = $request->input('body_type');
        $service->power         = $request->input('power');
        
        // Dacă ai coloane pentru Engine Size, VIN, Color, le pui aici:
        // $service->engine_size = $request->input('engine_size');
        // $service->vin = $request->input('vin');

        // SLUG
        $words     = Str::of($validated['title'])->explode(' ')->take(5)->implode(' ');
        $baseSlug  = Str::slug($words);
        $uniqueSlug = $baseSlug;
        $i         = 2;
        while (Service::where('slug', $uniqueSlug)->exists()) {
            $uniqueSlug = $baseSlug . '-' . $i;
            $i++;
        }
        $service->slug   = $uniqueSlug;
        $service->status = 'active';

        // IMAGINI
        $savedImages = [];
        if ($request->hasFile('images')) {
            $manager     = new ImageManager(new Driver());
            $seoBaseName = $baseSlug;

            foreach ($request->file('images') as $image) {
                if (count($savedImages) >= 10) break;

                $name = $seoBaseName . '-' . Str::random(6) . '.jpg';
                $path = storage_path('app/public/services/' . $name);

                if (!file_exists(dirname($path))) {
                    mkdir(dirname($path), 0755, true);
                }

                $manager->read($image->getRealPath())
                    ->scaleDown(1600)
                    ->toJpeg(75)
                    ->save($path);

                $savedImages[] = $name;
            }
        }

        $service->images = $savedImages;
        $service->save();

        return redirect()->to($service->public_url)
            ->with('success', 'Anunțul a fost publicat!');
    }

    // ==========================================
    // 6. EDIT (ACTUALIZAT SĂ TRIMITĂ $carData)
    // ==========================================
    public function edit($id)
    {
        $service = Service::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Refolosim logica din create() pentru a popula dropdown-urile și la editare
        $brands = CarBrand::orderBy('name')->get();
        $models = CarModel::with(['generations' => function($q) {
            $q->orderBy('year_start', 'asc');
        }])->get();

        $carData = [];
        foreach ($models as $model) {
            if (!$model->brand) continue;
            $brandName = $model->brand->name;
            $modelName = $model->name;

            if ($model->generations->isNotEmpty()) {
                foreach ($model->generations as $gen) {
                    $carData[$brandName][$modelName][] = [
                        'name'  => $gen->name,
                        'start' => $gen->year_start,
                        'end'   => $gen->year_end
                    ];
                }
            } else {
                if (!isset($carData[$brandName][$modelName])) {
                    $carData[$brandName][$modelName] = [];
                }
            }
        }

        return view('services.edit', [
            'service'    => $service,
            'categories' => Category::all(),
            'counties'   => County::all(),
            'brands'     => $brands,
            'carData'    => $carData, // Trimitem asta și la Edit
        ]);
    }

    // ==========================================
    // 7. UPDATE (ACTUALIZAT VALIDARE)
    // ==========================================
    public function update(Request $request, $id)
    {
        $service = Service::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $validated = $request->validate([
            'title'       => 'required|max:255',
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'county_id'   => 'required|exists:counties,id',
            'phone'       => 'nullable|string|max:30',
            'email'       => 'nullable|email|max:120',
            'price_value' => 'nullable|numeric',
            'price_type'  => 'required|in:fixed,negotiable',
            'currency'    => 'required|in:RON,EUR',
            'images.*'    => 'image|mimes:jpeg,png,jpg,webp|max:15360',

            // Câmpuri AUTO
            'brand'        => 'nullable|string|max:100',
            'model'        => 'nullable|string|max:100',
            'year'         => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'mileage'      => 'nullable|numeric',
            'fuel_type'    => 'nullable|string|max:50',
            'transmission' => 'nullable|string|max:50',
            'body_type'    => 'nullable|string|max:100',
            'power'        => 'nullable|integer',
        ]);

        $finalImages = $service->images;
        if (is_string($finalImages)) {
            $finalImages = json_decode($finalImages, true);
        }
        if (!is_array($finalImages)) {
            $finalImages = [];
        }

        // Mapare manuală pentru câmpurile care diferă ca nume în DB vs Form
        $service->title = $validated['title'];
        $service->description = $validated['description'];
        $service->price_value = $request->price_value;
        $service->year_of_fabrication = $request->input('year'); 
        $service->gearbox = $request->input('transmission');
        // Restul se face automat prin fill, dar scoatem images din validated
        unset($validated['images']);
        unset($validated['year']); // am asignat manual mai sus
        unset($validated['transmission']);
        
        $service->fill($validated);

        if ($request->hasFile('images')) {
            $manager    = new ImageManager(new Driver());
            $countyName = County::find($validated['county_id'])->name ?? 'romania';
            $seoBaseName = Str::slug($validated['title'] . '-' . $countyName);

            foreach ($request->file('images') as $image) {
                if (count($finalImages) >= 10) break;

                $name = $seoBaseName . '-' . Str::random(6) . '.jpg';
                $path = storage_path('app/public/services/' . $name);

                if (!file_exists(dirname($path))) {
                    mkdir(dirname($path), 0755, true);
                }

                $manager->read($image->getRealPath())
                    ->scaleDown(1600)
                    ->toJpeg(75)
                    ->save($path);

                $finalImages[] = $name;
            }
        }

        $service->images = $finalImages;
        $service->save();

        return redirect('/contul-meu?tab=anunturi')
            ->with('success', 'Modificat cu succes!');
    }

    // ==========================================
    // 8. DELETE IMAGE (NESCHIMBAT)
    // ==========================================
    public function deleteImage(Request $request, $id)
    {
        $service   = Service::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $imageName = $request->input('image');

        $currentImages = $service->images;
        if (is_string($currentImages)) {
            $currentImages = json_decode($currentImages, true);
        }
        if (!is_array($currentImages)) {
            $currentImages = [];
        }

        $key = array_search($imageName, $currentImages);

        if ($key !== false) {
            $path = storage_path('app/public/services/' . $imageName);
            if (file_exists($path)) {
                unlink($path);
            }

            unset($currentImages[$key]);
            $service->images = array_values($currentImages);
            $service->save();

            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }

    // ==========================================
    // 9. DESTROY (NESCHIMBAT)
    // ==========================================
    public function destroy($id)
    {
        try {
            $service = Service::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

            $images = $service->images;
            if (is_null($images)) {
                $images = [];
            } elseif (is_string($images)) {
                $images = json_decode($images, true) ?? [];
            }

            if (is_array($images)) {
                foreach ($images as $img) {
                    if (empty($img)) continue;
                    $path = storage_path("app/public/services/" . $img);
                    if (file_exists($path)) {
                        @unlink($path);
                    }
                }
            }

            $service->images = null;
            $service->save();
            $service->delete();

            return response()->json(['status' => 'deleted']);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ==========================================
    // 10. RENEW (NESCHIMBAT)
    // ==========================================
    public function renew($id)
    {
        $service             = Service::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $service->status     = 'active';
        $service->created_at = now();
        $service->save();
        return back()->with('success', 'Reînnoit!');
    }

    // ==========================================
    // 11. AJAX HELPER (OPȚIONAL)
    // ==========================================
    public function getModelsByBrand(Request $request)
    {
        $brandId = $request->get('brand_id');

        if (!$brandId) {
            return response()->json([]);
        }

        $models = CarModel::where('brand_id', $brandId)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return response()->json($models);
    }
}