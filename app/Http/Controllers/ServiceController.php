<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessServiceImages;
use App\Models\Service;
use App\Models\Category;
use App\Models\County;
use App\Models\Locality;
use App\Models\User;
use App\Notifications\ServicePublishedConfirmation;
use App\Support\ServiceImageStorage;

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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
    private const MAX_SERVICE_IMAGES = 10;
    private const MAX_SERVICE_IMAGE_KB = 15360;

    // ==========================================
    // 1. INDEX (NESCHIMBAT)
    // ==========================================
    public function index(Request $request)
{
    if (!$request->ajax()) {
        $canonicalRedirect = $this->redirectToCleanAutoListingUrl($request);
        if ($canonicalRedirect) {
            return $canonicalRedirect;
        }
    }

    $isHomepage = $request->routeIs('services.index');
    $page = max(1, (int) $request->get('page', 1));
    $perPageHomepage = 12;
    $perPageFirst = 10;
    $perPageNext  = 8;

    if ($isHomepage) {
        $limit = $perPageHomepage;
        $offset = 0;
    } elseif ($page === 1) {
        $limit  = $perPageFirst;
        $offset = 0;
    } else {
        $limit  = $perPageNext;
        $offset = $perPageFirst + (($page - 2) * $perPageNext);
    }

    $query = Service::with([
        'county',
        'locality',
        'category',
        'user',
        'combustibil',
        'cutieViteze',
        'brandRel',
        'modelRel',
        'generation.model.brand',
        'normaPoluare',
    ])->where('status', 'active');

    // Search
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    $countyFilter = null;
    if ($request->filled('county_id')) {
        $countyFilter = $request->county_id;
    } elseif ($request->filled('county')) {
        $countyFilter = $request->county;
    }

    $selectedLocality = null;
    if ($request->filled('locality_id')) {
        $selectedLocality = Locality::query()
            ->cities()
            ->select('id', 'county_id', 'name', 'slug')
            ->find($request->locality_id);

        if ($selectedLocality) {
            $query->where('locality_id', $selectedLocality->id);
        }
    }

    if ($countyFilter) {
        $query->where('county_id', $countyFilter);
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

    $yearMin = $request->input('year_min', $request->input('an_min'));
    $yearMax = $request->input('year_max', $request->input('an_max'));
    $priceMin = $request->input('price_min', $request->input('pret_min'));
    $priceMax = $request->input('price_max', $request->input('pret_max'));
    $kmMin = $request->input('km_min');
    $kmMax = $request->input('km_max');

    if ($yearMin !== null && $yearMin !== '') {
        $query->where('an_fabricatie', '>=', (int) $yearMin);
    }

    if ($yearMax !== null && $yearMax !== '') {
        $query->where('an_fabricatie', '<=', (int) $yearMax);
    }

    if ($kmMin !== null && $kmMin !== '') {
        $query->where('km', '>=', (int) $kmMin);
    }

    if ($kmMax !== null && $kmMax !== '') {
        $query->where('km', '<=', (int) $kmMax);
    }

    if (($priceMin !== null && $priceMin !== '') || ($priceMax !== null && $priceMax !== '')) {
        $query->where('currency', 'EUR');
    }

    if ($priceMin !== null && $priceMin !== '') {
        $query->where('price_value', '>=', (float) $priceMin);
    }

    if ($priceMax !== null && $priceMax !== '') {
        $query->where('price_value', '<=', (float) $priceMax);
    }

    // ================= FILTRU "DE UNDE CUMPERI" (TABURI) =================
    if ($request->filled('seller_type') && in_array($request->seller_type, ['individual', 'dealer'], true)) {
        $sellerType = $request->seller_type;
        $query->whereHas('user', function ($q) use ($sellerType) {
            $q->where('user_type', $sellerType);
        });
    }

    $sort = $request->get('sort', 'newest');
    $totalCount = $query->count();

    switch ($sort) {
        case 'price_asc':
            $query->orderBy('price_value', 'asc')->orderBy('created_at', 'desc');
            break;
        case 'price_desc':
            $query->orderBy('price_value', 'desc')->orderBy('created_at', 'desc');
            break;
        case 'km_asc':
            $query->orderBy('km', 'asc')->orderBy('created_at', 'desc');
            break;
        case 'power_asc':
            $query->orderBy('putere', 'asc')->orderBy('created_at', 'desc');
            break;
        default:
            $query->orderBy('created_at', 'desc');
    }

    $services = $query
        ->offset($offset)
        ->limit($limit)
        ->get();

    $loadedSoFar = $offset + $services->count();
    $hasMore     = $loadedSoFar < $totalCount;

    if ($request->ajax() || (string) $request->input('ajax') === '1') {
        $cardsView = $request->routeIs('services.index')
            ? 'services.partials.service_cards_home'
            : 'services.partials.service_cards_horizontal';
        $html = view($cardsView, ['services' => $services])->render();

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

    $view = $request->routeIs('services.index') ? 'services.index' : 'services.listing';
    $showEarlyStageBanners = true; // TEMP: Seteaza false cand site-ul are suficiente anunturi.
    $listingHasActiveFilters = !$isHomepage && $this->listingHasActiveFilters($request);

    return view($view, [
        'services'        => $services,
        'hasMore'         => $hasMore,
        'totalCount'      => $totalCount,
        'showEarlyStageBanners' => $showEarlyStageBanners,
        'listingHasActiveFilters' => $listingHasActiveFilters,
        'counties'        => $counties,
        'categories'      => $categories,
        'currentCategory' => $request->attributes->get('currentCategory'),
        'currentCounty'   => $request->attributes->get('currentCounty'),
        'currentLocality' => $request->attributes->get('currentLocality') ?: $selectedLocality,

        'brands'          => $brands,
        'bodies'          => $bodies,
        'fuels'           => $fuels,
        'transmissions'   => $transmissions,
        'carData'         => $carData,

        'currentBrand'    => $request->attributes->get('currentBrand'),
        'currentModel'    => $request->attributes->get('currentModel'),
    ]);
}

public function showDealerPortfolio(Request $request, string $countySlug, string $citySlug, string $dealerSlug)
{
    $dealer = User::findDealerByRouteSlug($dealerSlug);

    abort_unless($dealer, 404);

    $canonicalUrl = $dealer->dealer_public_url;
    if ($canonicalUrl && $request->url() !== $canonicalUrl) {
        return redirect()->to($canonicalUrl, 301);
    }

    $baseQuery = Service::query()
        ->where('status', 'active')
        ->where('user_id', $dealer->id);

    $brandIds = (clone $baseQuery)
        ->whereNotNull('brand_id')
        ->distinct()
        ->pluck('brand_id')
        ->filter()
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values();

    $brands = CarBrand::query()
        ->whereIn('id', $brandIds)
        ->orderBy('name')
        ->get(['id', 'name', 'slug']);

    $selectedBrandId = $request->integer('brand_id') ?: null;
    if ($selectedBrandId && ! $brandIds->contains($selectedBrandId)) {
        $selectedBrandId = null;
    }

    $modelIds = (clone $baseQuery)
        ->whereNotNull('model_id')
        ->distinct()
        ->pluck('model_id')
        ->filter()
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values();

    $availableModels = CarModel::query()
        ->whereIn('id', $modelIds)
        ->whereIn('car_brand_id', $brandIds)
        ->orderBy('name')
        ->get(['id', 'car_brand_id', 'name', 'slug']);

    $carData = $availableModels
        ->groupBy('car_brand_id')
        ->map(fn ($models) => $models
            ->map(fn ($model) => [
                'id' => $model->id,
                'name' => $model->name,
                'slug' => $model->slug,
                'generations' => [],
            ])
            ->values()
        )
        ->toArray();

    $models = $selectedBrandId
        ? $availableModels->where('car_brand_id', $selectedBrandId)->values()
        : collect();

    $selectedModelId = $selectedBrandId ? ($request->integer('model_id') ?: null) : null;
    if ($selectedModelId && ! $models->pluck('id')->contains($selectedModelId)) {
        $selectedModelId = null;
    }

    $servicesQuery = Service::with([
        'county',
        'locality',
        'category',
        'user',
        'combustibil',
        'cutieViteze',
        'brandRel',
        'modelRel',
        'generation.model.brand',
        'normaPoluare',
    ])
        ->where('status', 'active')
        ->where('user_id', $dealer->id);

    if ($selectedBrandId) {
        $servicesQuery->where('brand_id', $selectedBrandId);
    }

    if ($selectedModelId) {
        $servicesQuery->where('model_id', $selectedModelId);
    }

    $totalCount = $servicesQuery->count();

    $services = $servicesQuery
        ->orderBy('created_at', 'desc')
        ->get();

    return view('services.dealer-portfolio', [
        'dealer' => $dealer,
        'services' => $services,
        'totalCount' => $totalCount,
        'brands' => $brands,
        'models' => $models,
        'carData' => $carData,
        'selectedBrandId' => $selectedBrandId,
        'selectedModelId' => $selectedModelId,
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

public function indexAutoPath(
    Request $request,
    string $segment1,
    ?string $segment2 = null,
    ?string $segment3 = null,
    ?string $segment4 = null
) {
    $request->attributes->set('originalAutoQuery', $request->query());

    $segments = array_values(array_filter([$segment1, $segment2, $segment3, $segment4], fn ($segment) => $segment !== null && $segment !== ''));
    $segments = array_map(fn ($segment) => Str::slug($segment), $segments);

    $brand = CarBrand::where('slug', $segments[0])->first();

    if ($brand) {
        $this->applyBrandRouteFilter($request, $brand);

        if (!isset($segments[1])) {
            return $this->index($request);
        }

        $model = CarModel::where('slug', $segments[1])
            ->where('car_brand_id', $brand->id)
            ->first();

        if ($model) {
            $this->applyModelRouteFilter($request, $model);

            if (isset($segments[2])) {
                $county = $this->findCountyBySlug($segments[2]);
                if (!$county) {
                    abort(404);
                }

                $this->applyCountyRouteFilter($request, $county);

                if (isset($segments[3])) {
                    $city = $this->findCityBySlug($segments[3], $county);
                    if (!$city) {
                        abort(404);
                    }

                    $this->applyCityRouteFilter($request, $city);
                }
            }

            return $this->index($request);
        }

        $county = $this->findCountyBySlug($segments[1]);
        if (!$county) {
            abort(404);
        }

        $this->applyCountyRouteFilter($request, $county);

        if (isset($segments[2])) {
            $city = $this->findCityBySlug($segments[2], $county);
            if (!$city || isset($segments[3])) {
                abort(404);
            }

            $this->applyCityRouteFilter($request, $city);
        }

        return $this->index($request);
    }

    $county = $this->findCountyBySlug($segments[0]);
    if (!$county) {
        abort(404);
    }

    $this->applyCountyRouteFilter($request, $county);

    if (isset($segments[1])) {
        $city = $this->findCityBySlug($segments[1], $county);
        if (!$city || isset($segments[2])) {
            abort(404);
        }

        $this->applyCityRouteFilter($request, $city);
    }

    return $this->index($request);
}

    // ==========================================
    // 3. SHOW (NESCHIMBAT)
    // ==========================================
    public function showCar(
        Request $request,
        string $brandSlug,
        string $modelSlug,
        string $countySlug,
        string $citySlug,
        string $slug,
        int $id
    ) {
        $service = Service::withTrashed()
            ->with([
            'category',
            'county',
            'locality',
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

        $canonicalUrl = $service->public_url;
        $canonicalPath = ltrim((string) parse_url($canonicalUrl, PHP_URL_PATH), '/');

        if ($canonicalPath && $request->path() !== $canonicalPath) {
            return redirect()->to($canonicalUrl, 301);
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
    $wasAuthenticated = Auth::check();

    $rules = [
        'title'       => 'required|max:255',
        'description' => 'required',
        'category_id' => 'required|exists:categories,id',
        'county_id'   => 'required|exists:counties,id',
        'locality_id' => [
            'required',
            Rule::exists('localities', 'id')
                ->where('county_id', $request->input('county_id'))
                ->whereIn('type', Locality::CITY_TYPES),
        ],
        'phone'       => 'required|string|max:30',
        'price_value' => 'nullable|numeric',
        'price_type'  => 'required|in:fixed,negotiable',
        'currency'    => 'required|in:RON,EUR',
        'name'        => 'nullable|string|max:255',
        'images'      => ['nullable', 'array', 'max:' . self::MAX_SERVICE_IMAGES],
        'images.*'    => 'image|mimes:jpeg,png,jpg,webp|max:' . self::MAX_SERVICE_IMAGE_KB,
        'primary_image_index' => 'nullable|integer|min:0|max:9',

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

    ];

    foreach (array_keys(Service::FEATURE_OPTIONS) as $field) {
        $rules[$field] = 'nullable|boolean';
    }

    if (!Auth::check() && $request->filled('email') && $request->filled('password')) {
        $rules['email']    = 'required|email|unique:users,email|max:120';
        $rules['password'] = 'required|string|min:6';
    }

    $messages = [
        'images.max'        => 'Poți încărca maxim 10 imagini.',
        'images.*.max'      => 'Una dintre imagini este prea mare (max 15MB).',
        'images.*.uploaded' => 'Eroare la încărcare server.',
    ];

    $validated = $request->validate($rules, $messages);
    $this->validateImageUploadLimits($request);

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

    $this->applyLocality($service, $request);

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

    foreach (array_keys(Service::FEATURE_OPTIONS) as $field) {
        $service->{$field} = $request->boolean($field);
    }

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
    $service->published_at = $service->published_at ?: now();

    $service->images = [];
    $service->save();

    $primaryPendingIndex = $this->validPrimaryPendingIndex($request);
    $pendingImages = $this->storePendingServiceImages($service, $request);
    if ($pendingImages) {
        $this->dispatchServiceImageProcessing($service->id, $pendingImages, true, $primaryPendingIndex);
    } else {
        $service->save();
    }

    $serviceOwner = Auth::user();
    if ($serviceOwner && (int) $serviceOwner->id === (int) $service->user_id) {
        try {
            $serviceOwner->notify(new ServicePublishedConfirmation($service));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    $redirectUrl = $wasAuthenticated
        ? url('/contul-meu?tab=anunturi')
        : route('cars.index');

    return redirect()->to($redirectUrl)
        ->with('success', 'Anunțul a fost trimis către aprobare. Îl procesăm și îl publicăm automat în scurt timp.');
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

    $rules = [
        'title'       => 'required|max:255',
        'description' => 'required',

        // categoria NU vine din UI (hidden sau deloc). O forțăm mai jos.
        // 'category_id' => ... (NU mai validăm din request)

        'county_id'   => 'required|exists:counties,id',
        'locality_id' => [
            'required',
            Rule::exists('localities', 'id')
                ->where('county_id', $request->input('county_id'))
                ->whereIn('type', Locality::CITY_TYPES),
        ],
        'phone'       => 'required|string|max:30',
        'email'       => 'nullable|email|max:120',
        'price_value' => 'nullable|numeric',
        'price_type'  => 'required|in:fixed,negotiable',
        'currency'    => 'required|in:RON,EUR',
        'images'      => ['nullable', 'array', 'max:' . self::MAX_SERVICE_IMAGES],
        'images.*'    => 'image|mimes:jpeg,png,jpg,webp|max:' . self::MAX_SERVICE_IMAGE_KB,
        'primary_image_index' => 'nullable|integer|min:0|max:9',
        'primary_existing_image' => 'nullable|string',

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

    ];

    foreach (array_keys(Service::FEATURE_OPTIONS) as $field) {
        $rules[$field] = 'nullable|boolean';
    }

    $validated = $request->validate($rules, [
        'images.max'        => 'Poți avea maxim 10 imagini în total.',
        'images.*.max'      => 'Una dintre imagini este prea mare (max 15MB).',
        'images.*.uploaded' => 'Eroare la încărcare server.',
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

    $this->applyLocality($service, $request);

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

    foreach (array_keys(Service::FEATURE_OPTIONS) as $field) {
        $service->{$field} = $request->boolean($field);
    }

    // IMAGINI (păstrezi ce aveai)
    $currentImages = $this->normalizeServiceImages($service->images);
    $this->validateImageUploadLimits($request, count($currentImages));
    $currentImages = $this->moveExistingImageToFront($currentImages, $request->input('primary_existing_image'));

    $service->images = $currentImages;
    $service->save();

    $primaryPendingIndex = $request->filled('primary_existing_image') ? null : $this->validPrimaryPendingIndex($request);
    $pendingImages = $this->storePendingServiceImages($service, $request, max(0, 10 - count($currentImages)));
    if ($pendingImages) {
        $this->dispatchServiceImageProcessing($service->id, $pendingImages, false, $primaryPendingIndex);
    }

    return redirect('/contul-meu?tab=anunturi')
        ->with('success', 'Anunțul a fost trimis către aprobare. Îl procesăm și îl publicăm automat în scurt timp.');
}

    // ==========================================
    // 9. DESTROY
    // ==========================================
    public function destroy(Request $request, $id)
    {
        try {
            $service = Service::where('id', $id)->where('user_id', auth()->id())->firstOrFail();

            $images = $service->images;
            if (is_null($images)) {
                $images = [];
            } elseif (is_string($images)) {
                $images = json_decode($images, true) ?? [];
            }

            ServiceImageStorage::deleteServiceImages($images);

            $service->images = null;
            $service->save();
            $service->delete();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['status' => 'deleted']);
            }

            return back()->with('success', 'Anunțul a fost șters.');
        } catch (\Exception $e) {
            if (!($request->expectsJson() || $request->ajax())) {
                return back()->with('error', 'Anunțul nu a putut fi șters.');
            }

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

        ServiceImageStorage::deleteImageFiles($imageName);

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
    public function renew(Request $request, $id)
    {
        $service             = Service::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $service->status     = 'active';
        $service->created_at = now();
        $service->published_at = now();
        $service->save();

        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Anuntul a fost reactualizat.',
            ]);
        }
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
                'slug'        => $model->slug,
                'generations' => $generations,
            ];
        }

        return $carData;
    }

    public function getLocalitiesByCounty(int $countyId)
    {
        $localities = Locality::query()
            ->cities()
            ->where('county_id', $countyId)
            ->orderBy('type')
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return response()->json($localities);
    }

    private function applyLocality(Service $service, Request $request): void
    {
        $locality = null;
        if ($request->filled('locality_id')) {
            $locality = Locality::query()
                ->cities()
                ->select('id', 'name', 'latitude', 'longitude', 'county_id')
                ->where('county_id', $service->county_id)
                ->find($request->locality_id);
        }

        if (Schema::hasColumn('services', 'locality_id')) {
            $service->locality_id = $locality?->id;
        }
        if (Schema::hasColumn('services', 'latitude')) {
            $service->latitude = $locality?->latitude;
        }
        if (Schema::hasColumn('services', 'longitude')) {
            $service->longitude = $locality?->longitude;
        }
        if ($locality) {
            $service->city = $locality->name;
        }
    }

    private function storePendingServiceImages(Service $service, Request $request, int $limit = 10): array
    {
        if ($limit <= 0 || !$request->hasFile('images')) {
            return [];
        }

        $storedPaths = [];
        $directory = 'service-image-queue/' . $service->id;

        foreach (array_slice($request->file('images'), 0, $limit) as $image) {
            if (!$image->isValid()) {
                continue;
            }

            $extension = strtolower($image->getClientOriginalExtension() ?: $image->guessExtension() ?: 'jpg');
            $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: 'jpg';
            $storedPath = $image->storeAs($directory, (string) Str::uuid() . '.' . $extension);

            if ($storedPath) {
                $storedPaths[] = $storedPath;
            }
        }

        return $storedPaths;
    }

    private function dispatchServiceImageProcessing(
        int $serviceId,
        array $pendingImages,
        bool $replaceExisting,
        ?int $primaryPendingIndex = null
    ): void {
        $dispatch = ProcessServiceImages::dispatch($serviceId, $pendingImages, $replaceExisting, $primaryPendingIndex);
        $queue = config('queue.service_images_queue');

        if (is_string($queue) && trim($queue) !== '') {
            $dispatch->onQueue(trim($queue));
        }
    }

    private function normalizeServiceImages(mixed $images): array
    {
        if (is_string($images)) {
            $images = json_decode($images, true) ?: [];
        }

        return is_array($images) ? array_values(array_filter($images)) : [];
    }

    private function validateImageUploadLimits(Request $request, int $existingCount = 0): void
    {
        $files = $request->file('images', []);
        $files = is_array($files) ? array_values(array_filter($files)) : [$files];

        if ($existingCount + count($files) > self::MAX_SERVICE_IMAGES) {
            throw ValidationException::withMessages([
                'images' => 'Poți avea maxim 10 imagini în total.',
            ]);
        }

        foreach ($files as $file) {
            if ($file && $file->getSize() > self::MAX_SERVICE_IMAGE_KB * 1024) {
                throw ValidationException::withMessages([
                    'images' => 'Una dintre imagini este prea mare (max 15MB).',
                ]);
            }
        }
    }

    private function validPrimaryPendingIndex(Request $request): ?int
    {
        if (!$request->hasFile('images') || !$request->filled('primary_image_index')) {
            return null;
        }

        $index = (int) $request->input('primary_image_index');
        $files = $request->file('images', []);

        return isset($files[$index]) ? $index : null;
    }

    private function moveExistingImageToFront(array $images, ?string $primaryImage): array
    {
        if (!$primaryImage || !in_array($primaryImage, $images, true)) {
            return $images;
        }

        return array_values(array_unique(array_merge(
            [$primaryImage],
            array_filter($images, fn ($image) => $image !== $primaryImage)
        )));
    }

    private function redirectToCleanAutoListingUrl(Request $request)
    {
        $isAutoListing = $request->routeIs('cars.index')
            || $request->routeIs('brand.*')
            || str_starts_with($request->path(), 'anunturi-auto-de-vanzare');

        $originalQuery = $this->originalAutoQuery($request);
        $hasFilterQuery = collect($originalQuery)->except(['page'])->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty();
        if (!$isAutoListing && !($request->routeIs('services.index') && $hasFilterQuery)) {
            return null;
        }

        if ($isAutoListing && !$this->hasDirtyAutoQuery($request, $originalQuery) && $this->pathUsesCanonicalSlugs($request)) {
            return null;
        }

        $brand = $request->attributes->get('currentBrand');
        $model = $request->attributes->get('currentModel');
        $county = $request->attributes->get('currentCounty');
        $city = $request->attributes->get('currentLocality');

        if (!$brand && $request->filled('brand_id')) {
            $brand = CarBrand::find($request->brand_id);
        }

        if (!$model && $request->filled('model_id')) {
            $model = CarModel::with('brand')->find($request->model_id);
            if (!$brand && $model?->brand) {
                $brand = $model->brand;
            }
        }

        if (!$city && $request->filled('locality_id')) {
            $city = Locality::query()
                ->with('county')
                ->cities()
                ->find($request->locality_id);
        }

        if (!$county && $city?->county) {
            $county = $city->county;
        }

        if (!$county && $request->filled('county_id')) {
            $county = County::find($request->county_id);
        }

        $path = $this->buildAutoListingPath(
            $brand?->slug,
            $model?->slug,
            $county?->slug,
            $city?->slug
        );
        $query = $this->cleanAdvancedQuery($request, $originalQuery);

        if (
            '/' . ltrim($request->path(), '/') === $path
            && !$this->hasDirtyAutoQuery($request, $originalQuery)
        ) {
            return null;
        }

        $target = $this->buildUrlWithQuery($path, $query);

        return redirect()->to($target, 301);
    }

    private function buildAutoListingPath(?string $brandSlug = null, ?string $modelSlug = null, ?string $countySlug = null, ?string $citySlug = null): string
    {
        $segments = ['anunturi-auto-de-vanzare'];

        if ($brandSlug) {
            $segments[] = Str::slug($brandSlug);

            if ($modelSlug) {
                $segments[] = Str::slug($modelSlug);
            }

            if ($countySlug) {
                $segments[] = Str::slug($countySlug);

                if ($citySlug) {
                    $segments[] = Str::slug($citySlug);
                }
            }
        } elseif ($countySlug) {
            $segments[] = Str::slug($countySlug);

            if ($citySlug) {
                $segments[] = Str::slug($citySlug);
            }
        }

        return '/' . implode('/', $segments);
    }

    private function cleanAdvancedQuery(Request $request, ?array $originalQuery = null): array
    {
        $originalQuery ??= $this->originalAutoQuery($request);
        $query = [];
        $queryValue = static function (array $sourceKeys) use ($originalQuery) {
            foreach ($sourceKeys as $sourceKey) {
                $value = $originalQuery[$sourceKey] ?? null;
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }

            return null;
        };

        $sellerType = $queryValue(['seller_type']);
        $vehicleType = $queryValue(['vehicle_type']);

        if ($sellerType && $sellerType !== 'all') {
            $query['seller_type'] = $sellerType;
        }

        if ($vehicleType && !in_array($vehicleType, ['anunturi-auto-de-vanzare', 'autoturisme'], true)) {
            $query['vehicle_type'] = $vehicleType;
        }

        $mapping = [
            'car_generation_id' => ['car_generation_id'],
            'caroserie_id'      => ['caroserie_id'],
            'combustibil_id'    => ['combustibil_id'],
            'cutie_viteze_id'   => ['cutie_viteze_id'],
            'pret_min'          => ['pret_min', 'price_min'],
            'pret_max'          => ['pret_max', 'price_max'],
            'km_min'            => ['km_min'],
            'km_max'            => ['km_max'],
            'an_min'            => ['an_min', 'year_min'],
            'an_max'            => ['an_max', 'year_max'],
            'search'            => ['search'],
        ];

        foreach ($mapping as $targetKey => $sourceKeys) {
            $value = $queryValue($sourceKeys);
            if ($value !== null) {
                $query[$targetKey] = $value;
            }
        }

        $sort = $queryValue(['sort']);
        if ($sort && $sort !== 'newest') {
            $query['sort'] = $sort;
        }

        $page = $queryValue(['page']);
        if ($page && (int) $page > 1) {
            $query['page'] = $page;
        }

        return $query;
    }

    private function hasDirtyAutoQuery(Request $request, ?array $originalQuery = null): bool
    {
        $query = collect($originalQuery ?? $this->originalAutoQuery($request))
            ->filter(fn ($value) => $value !== null && $value !== '');

        if ($query->isEmpty()) {
            return false;
        }

        if ($query->has('vehicle_type') && in_array($query->get('vehicle_type'), ['anunturi-auto-de-vanzare', 'autoturisme'], true)) {
            return true;
        }

        if ($query->get('seller_type') === 'all') {
            return true;
        }

        if ($query->get('sort') === 'newest') {
            return true;
        }

        if ($query->has('page') && (int) $query->get('page') <= 1) {
            return true;
        }

        return count(array_intersect(
            array_keys($query->all()),
            ['brand_id', 'model_id', 'county_id', 'locality_id', 'price_min', 'price_max', 'year_min', 'year_max']
        )) > 0;
    }

    private function listingHasActiveFilters(Request $request): bool
    {
        if (
            $request->attributes->has('currentBrand')
            || $request->attributes->has('currentModel')
            || $request->attributes->has('currentCounty')
            || $request->attributes->has('currentLocality')
        ) {
            return true;
        }

        $originalQuery = $this->originalAutoQuery($request);

        if (!empty($this->cleanAdvancedQuery($request, $originalQuery))) {
            return true;
        }

        return collect($originalQuery)
            ->only(['brand_id', 'model_id', 'county_id', 'locality_id'])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->isNotEmpty();
    }

    private function originalAutoQuery(Request $request): array
    {
        $originalQuery = $request->attributes->get('originalAutoQuery');

        if (is_array($originalQuery)) {
            return $originalQuery;
        }

        return $request->query();
    }

    private function pathUsesCanonicalSlugs(Request $request): bool
    {
        $segments = explode('/', trim($request->path(), '/'));
        array_shift($segments);

        foreach ($segments as $segment) {
            if ($segment !== Str::slug($segment)) {
                return false;
            }
        }

        return true;
    }

    private function buildUrlWithQuery(string $path, array $query = []): string
    {
        $url = url($path);

        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

    private function findCountyBySlug(string $slug): ?County
    {
        return County::where('slug', $slug)->first();
    }

    private function findCityBySlug(string $slug, ?County $county = null): ?Locality
    {
        $query = Locality::query()
            ->with('county')
            ->cities()
            ->where('slug', $slug);

        if ($county) {
            $query->where('county_id', $county->id);
        }

        return $query->first();
    }

    private function applyBrandRouteFilter(Request $request, CarBrand $brand): void
    {
        $request->merge(['brand_id' => $brand->id]);
        $request->attributes->set('currentBrand', $brand);
    }

    private function applyModelRouteFilter(Request $request, CarModel $model): void
    {
        $request->merge(['model_id' => $model->id]);
        $request->attributes->set('currentModel', $model);
    }

    private function applyCountyRouteFilter(Request $request, County $county): void
    {
        $request->merge(['county_id' => $county->id]);
        $request->attributes->set('currentCounty', $county);
    }

    private function applyCityRouteFilter(Request $request, Locality $city): void
    {
        $request->merge([
            'county_id'   => $city->county_id,
            'locality_id' => $city->id,
        ]);

        if ($city->county) {
            $request->attributes->set('currentCounty', $city->county);
        }

        $request->attributes->set('currentLocality', $city);
    }
}
