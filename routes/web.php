<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminServiceController;
use App\Http\Controllers\Admin\AdminCategoryController;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/', [ServiceController::class, 'index'])->name('services.index');

// Old auto URLs keep their SEO value and move permanently to /anunturi-auto.
Route::get(
    '/autoturisme/{brandSlug}/{modelSlug}/{year}/{countySlug}/{id}',
    [ServiceController::class, 'redirectOldCarShow']
)->where([
    'year' => '[0-9]{4}',
    'id'   => '[0-9]+',
]);

Route::get('/autoturisme/{path?}', [ServiceController::class, 'redirectOldAutoUrl'])
    ->where('path', '.*');

Route::get('/adauga-anunt', fn () => redirect()->route('services.create', [], 301));
Route::get('/anunturi-auto/adauga-anunt', [ServiceController::class, 'create'])->name('services.create');
Route::post('/anunturi-auto/adauga-anunt', [ServiceController::class, 'store'])->name('services.store');
Route::get('/anunturi-auto/{id}/edit', [ServiceController::class, 'edit'])
    ->middleware('auth')
    ->whereNumber('id')
    ->name('services.edit');

// Detail URL: /anunturi-auto/{marca}/{model}/{oras}/{titlu}-{id}
Route::get(
    '/anunturi-auto/{brandSlug}/{modelSlug}/{citySlug}/{slug}-{id}',
    [ServiceController::class, 'showCar']
)->where([
    'id'   => '[0-9]+',
    'slug' => '.*',
])->name('service.show.car');

// Listing URLs:
// /anunturi-auto
// /anunturi-auto/{oras}
// /anunturi-auto/{marca}
// /anunturi-auto/{marca}/{model}
// /anunturi-auto/{marca}/{model}/{oras}
Route::get('/anunturi-auto', [ServiceController::class, 'index'])->name('cars.index');
Route::get('/anunturi-auto/{brandSlug}', [ServiceController::class, 'indexAutoSegment'])
    ->name('brand.index');
Route::get('/anunturi-auto/{brandSlug}/{modelSlug}', [ServiceController::class, 'indexBrandModel'])
    ->name('brand.model.index');
Route::get('/anunturi-auto/{brandSlug}/{modelSlug}/{citySlug}', [ServiceController::class, 'indexBrandModelCity'])
    ->name('brand.model.city.index');

Route::get('/contul-meu', function () {
    return view('account.index');
})->middleware('auth')->name('account.index');

/*
|--------------------------------------------------------------------------
| AJAX routes
|--------------------------------------------------------------------------
*/

Route::get('/api/models/{brandId}', [CarController::class, 'getModels'])->name('api.cars.models');
Route::get('/api/generations/{modelId}', [CarController::class, 'getGenerations'])->name('api.cars.generations');
Route::get('/api/localities/{countyId}', [ServiceController::class, 'getLocalitiesByCounty'])->name('api.localities.by.county');

Route::get('/ajax/models-by-brand', [ServiceController::class, 'getModelsByBrand'])
    ->name('ajax.models.by.brand');

Route::post('/profile/check-name', [ProfileController::class, 'checkName'])
    ->name('profile.checkName');

Route::post('/profile/check-company-name', [ProfileController::class, 'checkCompanyName'])
    ->middleware('auth')
    ->name('profile.checkCompanyName');

Route::post('/profile/check-email', [ProfileController::class, 'checkEmail'])
    ->name('profile.checkEmail');

Route::post('/profile/ajax-update', [ProfileController::class, 'ajaxUpdate'])
    ->middleware('auth')
    ->name('profile.ajaxUpdate');

/*
|--------------------------------------------------------------------------
| Protected routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/anunt/{id}/edit', fn ($id) => redirect()->route('services.edit', $id, 301))
        ->whereNumber('id');

    Route::put('/anunturi-auto/{id}', [ServiceController::class, 'update'])
        ->whereNumber('id')
        ->name('services.update');
    Route::delete('/anunturi-auto/{id}', [ServiceController::class, 'destroy'])
        ->whereNumber('id')
        ->name('services.destroy');
    Route::post('/anunturi-auto/{id}', [ServiceController::class, 'renew'])
        ->whereNumber('id')
        ->name('services.renew');

    Route::delete('/anunturi-auto/{id}/image', [ServiceController::class, 'deleteImage'])
        ->whereNumber('id')
        ->name('services.deleteImage');
    Route::delete('/services/{id}/image', [ServiceController::class, 'deleteImage'])
        ->whereNumber('id');

    Route::post('/favorite/toggle', [FavoriteController::class, 'toggle'])->name('favorite.toggle');
});

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin.access'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users', [AdminUserController::class, 'bulkAction'])->name('users.bulk');
        Route::post('/users/{id}/toggle', [AdminUserController::class, 'toggle'])->name('users.toggle');
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        Route::get('/services', [AdminServiceController::class, 'index'])->name('services.index');
        Route::delete('/services/{id}', [AdminServiceController::class, 'destroy'])->name('services.destroy');
        Route::post('/services/{id}/toggle', [AdminServiceController::class, 'toggle'])->name('services.toggle');
        Route::post('/services/bulk', [AdminServiceController::class, 'bulkAction'])->name('services.bulk');

        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [AdminCategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{id}/edit', [AdminCategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{id}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/counties', fn () => 'counties page')->name('counties.index');
    });

/*
|--------------------------------------------------------------------------
| Auth routes
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| Static pages
|--------------------------------------------------------------------------
*/

Route::view('/despre-noi', 'services.about')->name('page.about');
Route::view('/contact', 'services.contact')->name('page.contact');
Route::view('/termeni-si-conditii', 'services.terms')->name('page.terms');
Route::view('/politica-confidentialitate', 'services.privacy')->name('page.privacy');

/*
|--------------------------------------------------------------------------
| Generic SEO routes
|--------------------------------------------------------------------------
| These routes must stay last.
*/

Route::get('/{category}', [ServiceController::class, 'indexLocation'])
    ->name('category.index');

Route::get('/{category}/{county}', [ServiceController::class, 'indexLocation'])
    ->name('category.location');

Route::get('/{category}/{county}/{slug}-{id}', [ServiceController::class, 'show'])
    ->where(['id' => '[0-9]+', 'slug' => '.*'])
    ->name('service.show');
