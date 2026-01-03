<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CarController;

// ADMIN CONTROLLERS
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminServiceController;
use App\Http\Controllers\Admin\AdminCategoryController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES – AUTOTURISME ONLY
|--------------------------------------------------------------------------
*/

// HOME = listă anunțuri auto
Route::get('/', [ServiceController::class, 'index'])->name('services.index');

// Alias opțional pentru /autoturisme (folosește aceeași listă)
Route::get('/autoturisme', [ServiceController::class, 'index'])->name('cars.index');

// Listare după marcă: /autoturisme/volkswagen
Route::get('/autoturisme/{brandSlug}', [ServiceController::class, 'indexBrand'])
    ->name('brand.index');

// Pagina de detaliu anunț auto:
// /autoturisme/{brandSlug}/{modelSlug}/{year}/{countySlug}/{id}
Route::get(
    '/autoturisme/{brandSlug}/{modelSlug}/{year}/{countySlug}/{id}',
    [ServiceController::class, 'showCar']
)->where([
    'year' => '[0-9]{4}',
    'id'   => '[0-9]+',
])->name('service.show.car');


// FORMULAR ADĂUGARE
Route::get('/adauga-anunt', [ServiceController::class, 'create'])->name('services.create');
Route::post('/adauga-anunt', [ServiceController::class, 'store'])->name('services.store');

// CONTUL MEU
Route::get('/contul-meu', function () {
    return view('account.index');
})->middleware('auth')->name('account.index');

/*
|--------------------------------------------------------------------------
| AJAX ROUTES – PROFILE + AUTO
|--------------------------------------------------------------------------
*/

// AUTO – modelele și generațiile (CarController)
Route::get('/api/models/{brandId}', [CarController::class, 'getModels'])->name('api.cars.models');
Route::get('/api/generations/{modelId}', [CarController::class, 'getGenerations'])->name('api.cars.generations');

// VECHIUL helper (dacă îl mai folosești)
Route::get('/ajax/models-by-brand', [ServiceController::class, 'getModelsByBrand'])
    ->name('ajax.models.by.brand');

// CHECK USERNAME
Route::post('/profile/check-name', [ProfileController::class, 'checkName'])
    ->name('profile.checkName');

Route::post('/profile/check-company-name', [ProfileController::class, 'checkCompanyName'])
    ->middleware('auth')
    ->name('profile.checkCompanyName');



// CHECK EMAIL
Route::post('/profile/check-email', [ProfileController::class, 'checkEmail'])
    ->name('profile.checkEmail');

// AJAX UPDATE PROFIL
Route::post('/profile/ajax-update', [ProfileController::class, 'ajaxUpdate'])
    ->middleware('auth')->name('profile.ajaxUpdate');


/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/anunt/{id}/edit', [ServiceController::class, 'edit'])->name('services.edit');
    Route::put('/anunt/{id}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/anunt/{id}', [ServiceController::class, 'destroy'])->name('services.destroy');

    Route::delete('/services/{id}/image', [ServiceController::class, 'deleteImage'])->name('services.deleteImage');

    Route::post('/anunt/{id}', [ServiceController::class, 'renew'])->name('services.renew');

    Route::post('/favorite/toggle', [FavoriteController::class, 'toggle'])->name('favorite.toggle');
});

/*
|--------------------------------------------------------------------------
| ADMIN PANEL
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin.access'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // USERS
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        
        // 🔥 RUTA ADAUGATA/FIXATA PENTRU BULK ACTIONS (POST)
        Route::post('/users', [AdminUserController::class, 'bulkAction'])->name('users.bulk');
        
        Route::post('/users/{id}/toggle', [AdminUserController::class, 'toggle'])->name('users.toggle');
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        // SERVICES
        Route::get('/services', [AdminServiceController::class, 'index'])->name('services.index');
        Route::delete('/services/{id}', [AdminServiceController::class, 'destroy'])->name('services.destroy');
        Route::post('/services/{id}/toggle', [AdminServiceController::class, 'toggle'])->name('services.toggle');
        Route::post('/services/bulk', [AdminServiceController::class, 'bulkAction'])->name('services.bulk');

        // CATEGORIES
        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [AdminCategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{id}/edit', [AdminCategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{id}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/counties', fn() => 'counties page')->name('counties.index');
    });

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| STATIC PAGES
|--------------------------------------------------------------------------
*/

Route::view('/despre-noi', 'services.about')->name('page.about');
Route::view('/contact', 'services.contact')->name('page.contact');
Route::view('/termeni-si-conditii', 'services.terms')->name('page.terms');
Route::view('/politica-confidentialitate', 'services.privacy')->name('page.privacy');


/*
|--------------------------------------------------------------------------
| BRAND SEO ROUTES (AUTOTURISME / MARCĂ)
|--------------------------------------------------------------------------
|
| Exemple: /autoturisme/audi
*/

Route::get('/autoturisme/{brandSlug}', [ServiceController::class, 'indexBrand'])
    ->name('brand.index');



/*
|--------------------------------------------------------------------------
| SEO ROUTES (CATEGORIE / CATEGORIE + JUDEȚ / ANUNȚ)
|--------------------------------------------------------------------------
|
| ⚠ Acestea trebuie să fie ultimele!
*/

// 1. Listare doar pe Categorie (ex: /electrician)
Route::get('/{category}', [ServiceController::class, 'indexLocation'])
    ->name('category.index');

// 2. Listare Categorie + Județ (ex: /electrician/arges)
Route::get('/{category}/{county}', [ServiceController::class, 'indexLocation'])
    ->name('category.location');

// 3. Afișare Anunț (ex: /electrician/arges/titlu-smart-102)
Route::get('/{category}/{county}/{slug}-{id}', [ServiceController::class, 'show'])
    ->where(['id' => '[0-9]+', 'slug' => '.*'])
    ->name('service.show');
