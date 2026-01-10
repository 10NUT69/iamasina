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

// 🔹 MODELE NOMENCLATOR
use App\Models\Combustibil;
use App\Models\Culoare;
use App\Models\Caroserie;
use App\Models\CutieViteze;

// 🔹 MODELE LOOKUP NOI (IMPORTANT pentru dropdown)
use App\Models\CuloareOpt;
use App\Models\NormaPoluare;
use App\Models\Tractiune;

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
    $page = (int) $request->get('page', 1);
    $perPageFirst = 10;
    $perPageNext  = 8;

    if ($page === 1) {
        $limit  = $perPageFirst;
        $offset = 0;
    } else {
        $limit  = $perPageNext;
        $offset = $perPageFirst + (($page - 2) * $perPageNext);
    }

    $query = Service::where('status', 'active');

    // Search
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // County: nou = county_id, vechi = county
    if ($request->filled('county_id')) {
        $query->where('county_id', $request->county_id);
    } elseif ($request->filled('county')) {
        $query->where('county_id', $request->county);
    }

    // Category: nou = category_id, vechi = category
    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    } elseif ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    // ================= FILTRE AUTO (pe ID-uri) =================

    // Brand: nou = brand_id (coloana din services)
    if ($request->filled('brand_id')) {
        $query->where('brand_id', $request->brand_id);
    } elseif ($request->filled('brand')) {
        // fallback vechi (brand name) - doar ca să nu rupi link-uri vechi
        $brandName = $request->brand;
        $query->whereHas('generation.model.brand', function ($q) use ($brandName) {
            $q->where('name', $brandName);
        });
    }

    // Model: nou = model_id (coloana din services)
    if ($request->filled('model_id')) {
        $query->where('model_id', $request->model_id);
    } elseif ($request->filled('model')) {
        // fallback vechi (model name)
        $modelName = $request->model;
        $query->whereHas('generation.model', function ($q) use ($modelName) {
            $q->where('name', $modelName);
        });
    }

    // Generație (deja pe ID)
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

    // ================= FILTRU "DE UNDE CUMPERI" (TABURI) =================
    if ($request->filled('seller_type') && in_array($request->seller_type, ['individual', 'dealer'], true)) {
        $sellerType = $request->seller_type;
        $query->whereHas('user', function ($q) use ($sellerType) {
            $q->where('user_type', $sellerType);
        });
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

    // Date pentru filtre
    $brands        = CarBrand::orderBy('name')->get();
    $bodies        = Caroserie::orderBy('nume')->get();
    $fuels         = Combustibil::orderBy('nume')->get();
    $transmissions = CutieViteze::orderBy('nume')->get();
    $counties      = County::orderBy('name')->get();
    $categories    = Category::orderBy('sort_order', 'asc')->get();
    $carData       = $this->buildCarData();

    return view('services.index', [
        'services'        => $services,
        'hasMore'         => $hasMore,
        'counties'        => $counties,
        'categories'      => $categories,
        'currentCategory' => $request->attributes->get('currentCategory'),
        'currentCounty'   => $request->attributes->get('currentCounty'),

        'brands'          => $brands,
        'bodies'          => $bodies,
        'fuels'           => $fuels,
        'transmissions'   => $transmissions,
        'carData'         => $carData,

        'currentBrand'    => $request->attributes->get('currentBrand'),
    ]);
}

// ==========================================
// INDEX LOCATION (aliniat pe ID-uri)
// ==========================================
public function indexLocation(Request $request, $categorySlug, $countySlug = null)
{
    $category = Category::where('slug', $categorySlug)->firstOrFail();
    $county = null;

    if ($countySlug) {
        $county = County::where('slug', $countySlug)->firstOrFail();
    }

    // Trimitem parametrii noi (category_id, county_id)
    $request->merge([
        'category_id' => $category->id,
        'county_id'   => $county ? $county->id : null,
    ]);

    $request->attributes->set('currentCategory', $category);
    if ($county) {
        $request->attributes->set('currentCounty', $county);
    }

    return $this->index($request);
}

// ==========================================
// INDEX BRAND (aliniat pe ID-uri)
// ==========================================
public function indexBrand(Request $request, string $brandSlug)
{
    $brand = CarBrand::where('slug', $brandSlug)->firstOrFail();

    // Trimitem brand_id (nu nume)
    $request->merge([
        'brand_id' => $brand->id,
    ]);

    $request->attributes->set('currentBrand', $brand);

    return $this->index($request);
}

    // ==========================================
    // 3. SHOW (NESCHIMBAT)
    // ==========================================
    public function showCar(
        string $brandSlug,
        string $modelSlug,
        int $year,
        string $countySlug,
        int $id
    ) {
        $service = Service::withTrashed()
            ->with([
    'category',
    'county',
    'user',

    // auto
    'generation.model.brand',
    'brandRel',
    'modelRel',

    'combustibil',
    'cutieViteze',
    'caroserie',
    'culoare',

    // ✅ NOI
    'tractiune',
    'normaPoluare',
    'culoareOpt',
])
            ->findOrFail($id);

        $generation = $service->generation;
        $model      = $generation ? $generation->model : null;
        $brand      = $model ? $model->brand : null;
        $county     = $service->county;

        $canonicalUrl = $service->public_url;

        if ($brand && $model && $county && $service->an_fabricatie) {
            if (
                $brand->slug !== $brandSlug
                || $model->slug !== $modelSlug
                || (int)$service->an_fabricatie !== (int)$year
                || $county->slug !== $countySlug
            ) {
                return redirect()->to($canonicalUrl, 301);
            }
        }

        if (!$service->trashed()) {
            $service->increment('views');
        }

        return view('services.show', compact('service'));
    }

    // ==========================================
    // 4. CREATE (ALINIAT CU create.blade NOU)
    // ==========================================
    public function create()
    {
        $brands = CarBrand::orderBy('name')->get();

        $colors        = Culoare::orderBy('nume')->get();
        $fuels         = Combustibil::orderBy('nume')->get();
        $bodies        = Caroserie::orderBy('nume')->get();
        $transmissions = CutieViteze::orderBy('nume')->get();
		$tractiuni = Tractiune::orderBy('sort_order')->orderBy('nume')->get();
		$normePoluare = NormaPoluare::orderBy('sort_order')->orderBy('nume')->get();
		$colorOpts = CuloareOpt::orderBy('id')->get(); // Mat / Metalizată / Perlat




        $carData = $this->buildCarData();
		$autoCategoryId = \App\Models\Category::where('slug', 'autoturisme')->value('id')
    ?? \App\Models\Category::where('name', 'Autoturisme')->value('id');


        return view('services.create', [
            'categories'    => Category::orderBy('sort_order', 'asc')->get(),
			'autoCategoryId' => $autoCategoryId,

            'counties'      => County::orderBy('name')->get(),

            'brands'        => $brands,
            'carData'       => $carData,

            'colors'        => $colors,
            'fuels'         => $fuels,
            'bodies'        => $bodies,
            'transmissions' => $transmissions,
			'tractiuni' => $tractiuni,
			'normePoluare' => $normePoluare,
			'colorOpts' => $colorOpts,


        ]);
    }

    // ==========================================
    // 5. STORE (salvează FK-uri)
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

        // ✅ dacă vrei parc/proprietar la creare anunț (guest)
        // dacă NU trimiți user_type din form, rămâne individual
        'user_type'    => 'nullable|in:individual,dealer',
        'company_name' => 'nullable|string|max:255|required_if:user_type,dealer',
        'cui'          => 'nullable|string|max:32',
        'dealer_phone' => 'nullable|string|max:32|required_if:user_type,dealer',
        'dealer_county'=> 'nullable|string|max:255',
        'dealer_city'  => 'nullable|string|max:255',
        'dealer_address'=> 'nullable|string|max:255',

        // FK-uri noi
        'brand_id'          => 'nullable|exists:car_brands,id',
        'model_id'          => 'nullable|exists:car_models,id',
        'car_generation_id' => 'nullable|exists:car_generations,id',

        // rest auto
        'an_fabricatie'         => 'required|integer',
        'km'                    => 'required|integer',
        'capacitate_cilindrica' => 'nullable|integer',
        'putere'                => 'nullable|integer',
        'vin'                   => 'nullable|string|max:17',

        'combustibil_id'  => 'nullable|integer',
        'cutie_viteze_id' => 'nullable|integer',
        'caroserie_id'    => 'nullable|integer',
        'culoare_id'      => 'nullable|integer',
        'tractiune_id'    => 'nullable|exists:tractiuni,id',
        'norma_poluare_id'=> 'nullable|exists:norme_poluare,id',
        'numar_usi'       => 'nullable|integer|min:2|max:6',
        'numar_locuri'    => 'nullable|integer|min:1|max:9',
        'culoare_opt_id'  => 'nullable|exists:culoare_opt,id',

        'importata'        => 'nullable|boolean',
        'avariata'         => 'nullable|boolean',
        'filtru_particule' => 'nullable|boolean',
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

    // 1) CALCULARE NUME UTILIZATOR (VISITOR)
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

    // 2) LOGICA USER
    $userId = null;

    if (Auth::check()) {
        $userId = Auth::id();
    } elseif ($request->filled('email') && $request->filled('password')) {

        // ✅ tip user (default individual dacă nu vine nimic din form)
        $userType = $request->input('user_type', 'individual');
        if (!in_array($userType, ['individual', 'dealer'], true)) {
            $userType = 'individual';
        }

        $user = User::create([
            'user_type' => $userType,
            'name'      => $calculatedName,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),

            // ✅ câmpuri parc auto doar dacă dealer
            'company_name' => $userType === 'dealer' ? $request->input('company_name') : null,
            'cui'          => $userType === 'dealer' ? $request->input('cui') : null,

            // atenție: ai deja "phone" la anunț; la user am pus dealer_phone ca să nu ciocnim
            'phone'        => $userType === 'dealer' ? $request->input('dealer_phone') : null,
            'county'       => $userType === 'dealer' ? $request->input('dealer_county') : null,
            'city'         => $userType === 'dealer' ? $request->input('dealer_city') : null,
            'address'      => $userType === 'dealer' ? $request->input('dealer_address') : null,
        ]);

        Auth::login($user);
        $userId = $user->id;
    }

    // 3) CREARE SERVICE
    $service          = new Service();
    $service->user_id = $userId;

    if (!$userId) {
        $service->contact_name = $calculatedName;
    }

    // Standard
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

    // FK-uri noi
    $service->brand_id          = $request->input('brand_id');
    $service->model_id          = $request->input('model_id');
    $service->car_generation_id = $request->input('car_generation_id');

    // Auto
    $service->an_fabricatie         = $request->input('an_fabricatie');
    $service->km                    = $request->input('km');
    $service->vin                   = $request->input('vin');
    $service->putere                = $request->input('putere');
    $service->capacitate_cilindrica = $request->input('capacitate_cilindrica');

    $service->combustibil_id  = $request->input('combustibil_id');
    $service->cutie_viteze_id = $request->input('cutie_viteze_id');
    $service->caroserie_id    = $request->input('caroserie_id');
    $service->culoare_id      = $request->input('culoare_id');
    $service->tractiune_id    = $request->input('tractiune_id');
    $service->norma_poluare_id= $request->input('norma_poluare_id');
    $service->numar_usi       = $request->input('numar_usi');
    $service->numar_locuri    = $request->input('numar_locuri');
    $service->culoare_opt_id  = $request->input('culoare_opt_id');

    $service->importata        = $request->boolean('importata');
    $service->avariata         = $request->boolean('avariata');
    $service->filtru_particule = $request->boolean('filtru_particule');

    // SLUG
    $words      = Str::of($validated['title'])->explode(' ')->take(5)->implode(' ');
    $baseSlug   = Str::slug($words);
    $uniqueSlug = $baseSlug;
    $i          = 2;

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

    // Redirect ca înainte
    if (Auth::check()) {
        return redirect('/contul-meu?tab=anunturi')
            ->with('success', 'Anunțul a fost publicat!');
    }

    $redirectUrl = $service->public_url;
    if (!$redirectUrl || !is_string($redirectUrl) || strlen($redirectUrl) < 5) {
        $redirectUrl = url('/anunt/' . $service->id);
    }

    return redirect()->to($redirectUrl)
        ->with('success', 'Anunțul a fost publicat!');
}

   // ==========================================
// 6. EDIT (ALINIAT CU create.blade NOU)
// Categoria rămâne invizibilă (autoCategoryId)
// ==========================================
public function edit($id)
{
    $service = Service::where('id', $id)
        ->where('user_id', auth()->id())
        ->with(['generation.model.brand'])
        ->firstOrFail();

    // EXACT ca la create()
    $brands = CarBrand::orderBy('name')->get();

    $colors        = Culoare::orderBy('nume')->get();
    $fuels         = Combustibil::orderBy('nume')->get();
    $bodies        = Caroserie::orderBy('nume')->get();
    $transmissions = CutieViteze::orderBy('nume')->get();

    $tractiuni   = Tractiune::orderBy('sort_order')->orderBy('nume')->get();
    $normePoluare = NormaPoluare::orderBy('sort_order')->orderBy('nume')->get();
    $colorOpts    = CuloareOpt::orderBy('id')->get(); // Mat / Metalizată / Perlat

    $carData = $this->buildCarData();

    $autoCategoryId = Category::where('slug', 'autoturisme')->value('id')
        ?? Category::where('name', 'Autoturisme')->value('id');

    return view('services.edit', [
        'service'        => $service,

        // ca în create (categoria e hidden în blade, dar ai nevoie de id-ul ei)
        'autoCategoryId' => $autoCategoryId,

        'counties'       => County::orderBy('name')->get(),

        'brands'         => $brands,
        'carData'        => $carData,

        'colors'         => $colors,
        'fuels'          => $fuels,
        'bodies'         => $bodies,
        'transmissions'  => $transmissions,

        'tractiuni'      => $tractiuni,
        'normePoluare'   => $normePoluare,
        'colorOpts'      => $colorOpts,
    ]);
}


    // ==========================================
    // 7. UPDATE (salvează FK-uri)
    // ==========================================
   public function update(Request $request, $id)
{
    $service = Service::where('id', $id)
        ->where('user_id', auth()->id())
        ->firstOrFail();

    // forțăm categoria Autoturisme (exact ca în create)
    $autoCategoryId = Category::where('slug', 'autoturisme')->value('id')
        ?? Category::where('name', 'Autoturisme')->value('id');

    $validated = $request->validate([
        'title'       => 'required|max:255',
        'description' => 'required',

        // categoria NU vine din UI (hidden sau deloc). O forțăm mai jos.
        // 'category_id' => ... (NU mai validăm din request)

        'county_id'   => 'required|exists:counties,id',
        'phone'       => 'required|string|max:30',
        'email'       => 'nullable|email|max:120',
        'price_value' => 'nullable|numeric',
        'price_type'  => 'required|in:fixed,negotiable',
        'currency'    => 'required|in:RON,EUR',
        'images.*'    => 'image|mimes:jpeg,png,jpg,webp|max:15360',

        // FK-uri (pe care le trimite create.blade)
        'brand_id'          => 'nullable|exists:car_brands,id',
        'model_id'          => 'nullable|exists:car_models,id',
        'car_generation_id' => 'nullable|exists:car_generations,id',

        // rest auto
        'an_fabricatie'         => 'required|integer',
        'km'                    => 'required|integer',
        'vin'                   => 'nullable|string|max:17',
        'putere'                => 'nullable|integer',
        'capacitate_cilindrica' => 'nullable|integer',

        'combustibil_id'    => 'nullable|integer',
        'cutie_viteze_id'   => 'nullable|integer',
        'caroserie_id'      => 'nullable|integer',
        'culoare_id'        => 'nullable|integer',

        // câmpurile NOI din create.blade
        'tractiune_id'      => 'nullable|exists:tractiuni,id',
        'norma_poluare_id'  => 'nullable|exists:norme_poluare,id',
        'numar_usi'         => 'nullable|integer|min:2|max:6',
        'numar_locuri'      => 'nullable|integer|min:1|max:9',

        // tu ai zis clar: tabelul e culoare_opt (singular)
        'culoare_opt_id'    => 'nullable|exists:culoare_opt,id',

        'importata'         => 'nullable|boolean',
        'avariata'          => 'nullable|boolean',
        'filtru_particule'  => 'nullable|boolean',
    ]);

    // Standard
    $service->title       = $request->input('title');
    $service->description = $request->input('description');

    // categoria e invizibilă => o setăm noi (ca în create)
    $service->category_id = $autoCategoryId;

    $service->county_id   = $request->input('county_id');
    $service->phone       = $request->input('phone');
    $service->email       = $request->input('email');
    $service->price_value = $request->input('price_value');
    $service->price_type  = $request->input('price_type');
    $service->currency    = $request->input('currency');

    // FK-uri
    $service->brand_id          = $request->input('brand_id');
    $service->model_id          = $request->input('model_id');
    $service->car_generation_id = $request->input('car_generation_id');

    // Auto
    $service->an_fabricatie         = $request->input('an_fabricatie');
    $service->km                    = $request->input('km');
    $service->vin                   = $request->input('vin');
    $service->putere                = $request->input('putere');
    $service->capacitate_cilindrica = $request->input('capacitate_cilindrica');

    $service->combustibil_id  = $request->input('combustibil_id');
    $service->cutie_viteze_id = $request->input('cutie_viteze_id');
    $service->caroserie_id    = $request->input('caroserie_id');
    $service->culoare_id      = $request->input('culoare_id');

    // NOI
    $service->tractiune_id      = $request->input('tractiune_id');
    $service->norma_poluare_id  = $request->input('norma_poluare_id');
    $service->numar_usi         = $request->input('numar_usi');
    $service->numar_locuri      = $request->input('numar_locuri');
    $service->culoare_opt_id    = $request->input('culoare_opt_id');

    $service->importata         = $request->boolean('importata');
    $service->avariata          = $request->boolean('avariata');
    $service->filtru_particule  = $request->boolean('filtru_particule');

    // IMAGINI (păstrezi ce aveai)
    $currentImages = $service->images;
    if (is_string($currentImages)) {
        $currentImages = json_decode($currentImages, true);
    }
    if (!is_array($currentImages)) {
        $currentImages = [];
    }

    if ($request->hasFile('images')) {
        $manager     = new ImageManager(new Driver());
        $countyName  = County::find($request->county_id)->name ?? 'romania';
        $seoBaseName = Str::slug($request->title . '-' . $countyName);

        foreach ($request->file('images') as $image) {
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

    $service->images = $currentImages;
    $service->save();

    return redirect('/contul-meu?tab=anunturi')
        ->with('success', 'Anunțul a fost actualizat!');
}

    // ==========================================
    // 9. DESTROY
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

    public function deleteImage(Request $request, $id)
    {
        $service = Service::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $imageName = $request->input('image');

        if (!$imageName) {
            return response()->json([
                'success' => false,
                'message' => 'Niciun fișier specificat.',
            ], 400);
        }

        $images = $service->images;

        if (is_null($images)) {
            $images = [];
        } elseif (is_string($images)) {
            $images = json_decode($images, true) ?? [];
        }

        if (!is_array($images)) {
            $images = [];
        }

        if (!in_array($imageName, $images, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Imaginea nu a fost găsită în acest anunț.',
            ], 404);
        }

        $path = storage_path('app/public/services/' . $imageName);
        if (file_exists($path)) {
            @unlink($path);
        }

        $images = array_values(array_filter($images, function ($img) use ($imageName) {
            return $img !== $imageName;
        }));

        $service->images = $images;
        $service->save();

        return response()->json([
            'success' => true,
        ]);
    }

    // ==========================================
    // 10. RENEW
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
    // 11. AJAX HELPER (coloana corectă: car_brand_id)
    // ==========================================
    public function getModelsByBrand(Request $request)
    {
        $brandId = $request->get('brand_id');

        if (!$brandId) {
            return response()->json([]);
        }

        $models = CarModel::where('car_brand_id', $brandId)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return response()->json($models);
    }

    // ==========================================
    // helper: buildCarData pe ID-uri
    // carData[brand_id] = [
    //   { id, name, generations: [ {id,name,start,end}, ... ] },
    // ]
    // ==========================================
    protected function buildCarData()
    {
        $models = CarModel::with([
            'brand',
            'generations' => function ($q) {
                $q->orderBy('year_start', 'asc');
            }
        ])->orderBy('name')->get();

        $carData = [];

        foreach ($models as $model) {
            if (!$model->brand) continue;

            $brandId = $model->brand->id;

            if (!isset($carData[$brandId])) {
                $carData[$brandId] = [];
            }

            $generations = [];
            if ($model->generations->isNotEmpty()) {
                foreach ($model->generations as $gen) {
                    $generations[] = [
                        'id'    => $gen->id,
                        'name'  => $gen->name,
                        'start' => $gen->year_start,
                        'end'   => $gen->year_end,
                    ];
                }
            }

            $carData[$brandId][] = [
                'id'          => $model->id,
                'name'        => $model->name,
                'generations' => $generations,
            ];
        }

        return $carData;
    }
}
