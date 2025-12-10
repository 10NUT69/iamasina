<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Category;
use App\Models\County;
use App\Models\User;

// 🔹 MODELE AUTO
use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\CarGeneration;

// 🔹 MODELE NOMENCLATOR (ADĂUGATE ACUM)
use App\Models\Combustibil;
use App\Models\Culoare;
use App\Models\Caroserie;
use App\Models\CutieViteze;

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

    // PORNIM DE LA ANUNȚURI ACTIVE
    $query = Service::where('status', 'active');

    // 🔍 Căutare text (dacă o mai folosești pe undeva)
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%$search%")
              ->orWhere('description', 'like', "%$search%");
        });
    }

    // FILTRE VECHI
    if ($request->filled('county')) {
        $query->where('county_id', $request->county);
    }

    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    // ================= FILTRE AUTO NOI =================

    // Marcă (prin relația generation -> model -> brand)
    if ($request->filled('brand')) {
        $brandName = $request->brand;
        $query->whereHas('generation.model.brand', function ($q) use ($brandName) {
            $q->where('name', $brandName);
        });
    }

    // Model
    if ($request->filled('model')) {
        $modelName = $request->model;
        $query->whereHas('generation.model', function ($q) use ($modelName) {
            $q->where('name', $modelName);
        });
    }

    // Generație
    if ($request->filled('car_generation_id')) {
        $query->where('car_generation_id', $request->car_generation_id);
    }

    // Caroserie
    if ($request->filled('caroserie_id')) {
        $query->where('caroserie_id', $request->caroserie_id);
    }

    // Combustibil
    if ($request->filled('combustibil_id')) {
        $query->where('combustibil_id', $request->combustibil_id);
    }

    // Cutie viteze
    if ($request->filled('cutie_viteze_id')) {
        $query->where('cutie_viteze_id', $request->cutie_viteze_id);
    }

    // Județ (dublăm și filtrul vechi dacă vine doar "county")
    if ($request->filled('county')) {
        $query->where('county_id', $request->county);
    }

    // ================= PAGINARE CUSTOM =================
    $totalCount = $query->count();

    $services = $query
        ->orderBy('created_at', 'desc')
        ->offset($offset)
        ->limit($limit)
        ->get();

    $loadedSoFar = $offset + $services->count();
    $hasMore     = $loadedSoFar < $totalCount;

    // ================= RĂSPUNS AJAX (infinite scroll) =================
    if ($request->ajax()) {
        $html = view('services.partials.service_cards', ['services' => $services])->render();

        return response()->json([
            'html'        => $html,
            'hasMore'     => $hasMore,
            'total'       => $totalCount,
            'loadedCount' => $services->count(),
        ]);
    }

    // ================= DATE PENTRU FILTRE (VIEW) =================
    $brands        = CarBrand::orderBy('name')->get();
    $bodies        = Caroserie::orderBy('nume')->get();
    $fuels         = Combustibil::orderBy('nume')->get();
    $transmissions = CutieViteze::orderBy('nume')->get();
    $counties      = County::orderBy('name')->get();
    $categories    = Category::orderBy('sort_order', 'asc')->get();
    $carData       = $this->buildCarData();   // helper de mai jos

    return view('services.index', [
        'services'        => $services,
        'hasMore'         => $hasMore,
        'counties'        => $counties,
        'categories'      => $categories,
        'currentCategory' => $request->attributes->get('currentCategory'),
        'currentCounty'   => $request->attributes->get('currentCounty'),

        // pentru index.blade nou:
        'brands'          => $brands,
        'bodies'          => $bodies,
        'fuels'           => $fuels,
        'transmissions'   => $transmissions,
        'carData'         => $carData,
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
    $service = Service::withTrashed()
        ->with([
            'category',
            'county',
            'user',
            'generation.model.brand',   // brand + model + generație
            'combustibil',
            'cutieViteze',
            'caroserie',
            'culoare',
        ])
        ->findOrFail($id);

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
    // 4. CREATE (MODIFICAT CHIRURGICAL)
    // ==========================================
    public function create()
    {
        // 1. Luăm brandurile
        $brands = CarBrand::orderBy('name')->get();

        // 2. [MODIFICARE] Luăm listele pentru Dropdown-uri
        $colors = Culoare::all(); 
        $fuels = Combustibil::all();
        $bodies = Caroserie::all();
        $transmissions = CutieViteze::all();

        // 3. Luăm Modelele + Generațiile
        $models = CarModel::with(['generations' => function($q) {
            $q->orderBy('year_start', 'asc');
        }])->get();

        // 4. Construim structura JSON pentru JavaScript
        $carData = [];

        foreach ($models as $model) {
            if (!$model->brand) continue;

            $brandName = $model->brand->name;
            $modelName = $model->name;

            if ($model->generations->isNotEmpty()) {
                foreach ($model->generations as $gen) {
                    $carData[$brandName][$modelName][] = [
                        'id'    => $gen->id,         // [MODIFICARE] Critic pentru DB!
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

        return view('services.create', [
            'categories' => Category::orderBy('sort_order', 'asc')->get(),
            'counties'   => County::all(),
            'brands'     => $brands,
            'carData'    => $carData,
            
            // [MODIFICARE] Trimitem variabilele noi
            'colors'        => $colors,
            'fuels'         => $fuels,
            'bodies'        => $bodies,
            'transmissions' => $transmissions
        ]);
    }

    // ==========================================
    // 5. STORE (MODIFICAT CHIRURGICAL)
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

            // 🔹 [MODIFICARE] VALIDARE CÂMPURI AUTO NOI
            'car_generation_id' => 'required|integer',
            'an_fabricatie'     => 'required|integer',
            'km'                => 'required|integer',
            'capacitate_cilindrica' => 'nullable|integer',
            'putere'            => 'nullable|integer',
            'vin'               => 'nullable|string|max:17',
            
            // Validăm ID-urile din dropdown-uri
            'combustibil_id'    => 'nullable|integer',
            'cutie_viteze_id'   => 'nullable|integer',
            'caroserie_id'      => 'nullable|integer',
            'culoare_id'        => 'nullable|integer',
            
            // Câmpurile vechi (opționale acum, le lăsăm ca să nu crape nimic)
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'year'  => 'nullable',
            'fuel_type' => 'nullable',
            'transmission' => 'nullable',
            'body_type' => 'nullable',
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

        // 1. CALCULARE NUME UTILIZATOR (VISITOR) - Neschimbat
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

        // 2. LOGICA USER - Neschimbat
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

        // 🔹 [MODIFICARE] Mapare date AUTO NOI
        // Acestea sunt câmpurile esențiale pentru filtrare
        $service->car_generation_id = $request->input('car_generation_id');
        $service->an_fabricatie     = $request->input('an_fabricatie');
        $service->km                = $request->input('km');
        $service->vin               = $request->input('vin');
        $service->putere            = $request->input('putere');
        $service->capacitate_cilindrica = $request->input('capacitate_cilindrica');

        // Dropdown-uri noi (salvăm ID-urile)
        $service->combustibil_id    = $request->input('combustibil_id');
        $service->cutie_viteze_id   = $request->input('cutie_viteze_id');
        $service->caroserie_id      = $request->input('caroserie_id');
        $service->culoare_id        = $request->input('culoare_id');

        

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

        // IMAGINI - Neschimbat
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
    // 6. EDIT (NESCHIMBAT - Pentru moment)
    // ==========================================
  public function edit($id)
    {
        $service = Service::where('id', $id)
    ->where('user_id', auth()->id())
    ->with(['generation.model.brand'])  // ← schimbat carModel în model
    ->firstOrFail();

        // 1. Listele necesare pentru dropdown-uri
        $categories = Category::all();
        $counties   = County::all();
        $brands     = CarBrand::orderBy('name')->get();
        
        $colors = \App\Models\Culoare::all(); 
        $fuels = \App\Models\Combustibil::all();
        $bodies = \App\Models\Caroserie::all();
        $transmissions = \App\Models\CutieViteze::all();

        // 2. Construim structura pentru JavaScript (CarData)
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
                        'id'    => $gen->id,
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

        return view('services.edit', compact(
            'service', 'categories', 'counties', 'brands', 'carData',
            'colors', 'fuels', 'bodies', 'transmissions'
        ));
    }
    // ==========================================
    // 7. UPDATE (NESCHIMBAT - Pentru moment)
    // ==========================================
   public function update(Request $request, $id)
    {
        $service = Service::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // 1. VALIDARE
        $validated = $request->validate([
            'title'       => 'required|max:255',
            'description' => 'required',
            'category_id' => 'required|exists:categories,id',
            'county_id'   => 'required|exists:counties,id',
            'phone'       => 'required|string|max:30',
            'email'       => 'nullable|email|max:120',
            'price_value' => 'nullable|numeric',
            'price_type'  => 'required|in:fixed,negotiable',
            'currency'    => 'required|in:RON,EUR',
            'images.*'    => 'image|mimes:jpeg,png,jpg,webp|max:15360',

            // Câmpurile noi (IDs)
            'car_generation_id' => 'required|integer',
            'an_fabricatie'     => 'required|integer',
            'km'                => 'required|integer',
            'vin'               => 'nullable|string|max:17',
            'putere'            => 'nullable|integer',
            'capacitate_cilindrica' => 'nullable|integer',
            
            // Dropdown-uri opționale
            'combustibil_id'    => 'nullable|integer',
            'cutie_viteze_id'   => 'nullable|integer',
            'caroserie_id'      => 'nullable|integer',
            'culoare_id'        => 'nullable|integer',
        ]);

        // 2. ATRIBUIRE DATE (MANUALĂ)
        $service->title       = $request->input('title');
        $service->description = $request->input('description');
        $service->category_id = $request->input('category_id');
        $service->county_id   = $request->input('county_id');
        $service->phone       = $request->input('phone');
        $service->email       = $request->input('email');
        $service->price_value = $request->input('price_value');
        $service->price_type  = $request->input('price_type');
        $service->currency    = $request->input('currency');

        // 🔹 DATE AUTO (NOILE COLOANE)
        $service->car_generation_id = $request->input('car_generation_id');
        $service->an_fabricatie     = $request->input('an_fabricatie');
        $service->km                = $request->input('km');
        $service->vin               = $request->input('vin');
        $service->putere            = $request->input('putere');
        $service->capacitate_cilindrica = $request->input('capacitate_cilindrica');

        $service->combustibil_id    = $request->input('combustibil_id');
        $service->cutie_viteze_id   = $request->input('cutie_viteze_id');
        $service->caroserie_id      = $request->input('caroserie_id');
        $service->culoare_id        = $request->input('culoare_id');

        // 3. PROCESARE IMAGINI
        // Luăm imaginile vechi
        $currentImages = $service->images;
        if (is_string($currentImages)) {
            $currentImages = json_decode($currentImages, true);
        }
        if (!is_array($currentImages)) {
            $currentImages = [];
        }

        // Adăugăm imaginile noi (dacă există)
        if ($request->hasFile('images')) {
            $manager = new ImageManager(new Driver());
            // Fallback name safe
            $countyName = County::find($request->county_id)->name ?? 'romania';
            $seoBaseName = Str::slug($request->title . '-' . $countyName);

            foreach ($request->file('images') as $image) {
                // Limită de 10 imagini total
                if (count($currentImages) >= 10) break;

                $name = $seoBaseName . '-' . Str::random(6) . '.jpg';
                $path = storage_path('app/public/services/' . $name);

                if (!file_exists(dirname($path))) {
                    mkdir(dirname($path), 0755, true);
                }

                $manager->read($image->getRealPath())
                    ->scaleDown(1600)
                    ->toJpeg(75)
                    ->save($path);

                $currentImages[] = $name;
            }
        }

        // Salvăm array-ul final înapoi în DB
        $service->images = $currentImages;
        
        $service->save();

        return redirect('/contul-meu?tab=anunturi')
            ->with('success', 'Anunțul a fost actualizat!');
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
    // 11. AJAX HELPER (NESCHIMBAT)
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
	protected function buildCarData()
{
    $models = CarModel::with([
        'brand',
        'generations' => function ($q) {
            $q->orderBy('year_start', 'asc');
        }
    ])->get();

    $carData = [];

    foreach ($models as $model) {
        if (!$model->brand) {
            continue;
        }

        $brandName = $model->brand->name;
        $modelName = $model->name;

        if ($model->generations->isNotEmpty()) {
            foreach ($model->generations as $gen) {
                $carData[$brandName][$modelName][] = [
                    'id'    => $gen->id,
                    'name'  => $gen->name,
                    'start' => $gen->year_start,
                    'end'   => $gen->year_end,
                ];
            }
        } else {
            if (!isset($carData[$brandName][$modelName])) {
                $carData[$brandName][$modelName] = [];
            }
        }
    }

    return $carData;
}

}