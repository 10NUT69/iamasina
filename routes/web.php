<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\SavedSearchController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminServiceController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminBackupController;
use App\Http\Controllers\Admin\AdminAutoCatalogController;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/', [ServiceController::class, 'index'])->name('services.index');

Route::get('/autoturisme/{path?}', fn () => abort(404))->where('path', '.*');
Route::get('/anunturi-auto/{path?}', fn () => abort(404))->where('path', '.*');

Route::get('/anunturi-auto-de-vanzare/adauga-anunt', [ServiceController::class, 'create'])->name('services.create');
Route::post('/anunturi-auto-de-vanzare/adauga-anunt', [ServiceController::class, 'store'])->name('services.store');
Route::get('/anunturi-auto-de-vanzare/{id}/edit', [ServiceController::class, 'edit'])
    ->middleware('auth')
    ->whereNumber('id')
    ->name('services.edit');

Route::get(
    '/anunturi-auto-de-vanzare/parc-auto/{countySlug}/{citySlug}/{dealerSlug}',
    [ServiceController::class, 'showDealerPortfolio']
)->name('dealers.show');

// Detail URL: /anunturi-auto-de-vanzare/{marca}/{model}/{judet}/{oras}/{titlu}-{id}
Route::get(
    '/anunturi-auto-de-vanzare/{brandSlug}/{modelSlug}/{countySlug}/{citySlug}/{slug}-{id}',
    [ServiceController::class, 'showCar']
)->where([
    'id'   => '[0-9]+',
    'slug' => '.*',
])->name('service.show.car');

// Listing URLs:
// /anunturi-auto-de-vanzare
// /anunturi-auto-de-vanzare/{judet}
// /anunturi-auto-de-vanzare/{judet}/{oras}
// /anunturi-auto-de-vanzare/{marca}
// /anunturi-auto-de-vanzare/{marca}/{model}
// /anunturi-auto-de-vanzare/{marca}/{model}/{judet}
// /anunturi-auto-de-vanzare/{marca}/{model}/{judet}/{oras}
Route::get('/anunturi-auto-de-vanzare', [ServiceController::class, 'index'])->name('cars.index');
Route::get('/anunturi-auto-de-vanzare/{segment1}', [ServiceController::class, 'indexAutoPath'])
    ->name('brand.index');
Route::get('/anunturi-auto-de-vanzare/{segment1}/{segment2}', [ServiceController::class, 'indexAutoPath'])
    ->name('brand.model.index');
Route::get('/anunturi-auto-de-vanzare/{segment1}/{segment2}/{segment3}', [ServiceController::class, 'indexAutoPath'])
    ->name('brand.model.county.index');
Route::get('/anunturi-auto-de-vanzare/{segment1}/{segment2}/{segment3}/{segment4}', [ServiceController::class, 'indexAutoPath'])
    ->name('brand.model.city.index');

Route::get('/contul-meu', function () {
    return view('account.index');
})->middleware('auth')->name('account.index');

Route::get('/dashboard', function () {
    return redirect()->route('account.index');
})->middleware('auth')->name('dashboard');

/*
|--------------------------------------------------------------------------
| AJAX routes
|--------------------------------------------------------------------------
*/

Route::get('/api/models/{brandId}', [CarController::class, 'getModels'])->name('api.cars.models');
Route::get('/api/generations/{modelId}', [CarController::class, 'getGenerations'])->name('api.cars.generations');
Route::get('/api/localities/{countyId}', [ServiceController::class, 'getLocalitiesByCounty'])->name('api.localities.by.county');

Route::get('/ajax/brands', [ServiceController::class, 'getBrands'])
    ->name('ajax.brands');
Route::get('/ajax/bodies', [ServiceController::class, 'getCarBodyTypes'])
    ->name('ajax.bodies');
Route::get('/ajax/fuels', [ServiceController::class, 'getFuelTypes'])
    ->name('ajax.fuels');
Route::get('/ajax/transmissions', [ServiceController::class, 'getTransmissionTypes'])
    ->name('ajax.transmissions');
Route::get('/ajax/counties', [ServiceController::class, 'getCounties'])
    ->name('ajax.counties');
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

Route::post('/profile/dealer-gallery', [ProfileController::class, 'uploadDealerGallery'])
    ->middleware('auth')
    ->name('profile.dealerGallery.upload');

Route::delete('/profile/dealer-gallery/{index}', [ProfileController::class, 'deleteDealerGalleryImage'])
    ->middleware('auth')
    ->whereNumber('index')
    ->name('profile.dealerGallery.delete');

/*
|--------------------------------------------------------------------------
| Protected routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/mesaje', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/mesaje/status/necitite', [MessageController::class, 'unreadCount'])->name('messages.unreadCount');
    Route::get('/mesaje/{conversation}/poll', [MessageController::class, 'poll'])
        ->whereNumber('conversation')
        ->name('messages.poll');
    Route::post('/mesaje/anunt/{service}', [MessageController::class, 'startFromService'])
        ->whereNumber('service')
        ->name('messages.startFromService');
    Route::post('/mesaje/{conversation}', [MessageController::class, 'store'])
        ->whereNumber('conversation')
        ->name('messages.store');
    Route::delete('/mesaje/conversatie/{conversation}', [MessageController::class, 'destroyConversation'])
        ->whereNumber('conversation')
        ->name('messages.destroyConversation');
    Route::delete('/mesaje/mesaj/{message}', [MessageController::class, 'destroyMessage'])
        ->whereNumber('message')
        ->name('messages.destroyMessage');

    Route::put('/anunturi-auto-de-vanzare/{id}', [ServiceController::class, 'update'])
        ->whereNumber('id')
        ->name('services.update');
    Route::delete('/anunturi-auto-de-vanzare/{id}', [ServiceController::class, 'destroy'])
        ->whereNumber('id')
        ->name('services.destroy');
    Route::post('/anunturi-auto-de-vanzare/{id}', [ServiceController::class, 'renew'])
        ->whereNumber('id')
        ->name('services.renew');

    Route::delete('/anunturi-auto-de-vanzare/{id}/image', [ServiceController::class, 'deleteImage'])
        ->whereNumber('id')
        ->name('services.deleteImage');

    Route::post('/favorite/toggle', [FavoriteController::class, 'toggle'])->name('favorite.toggle');
    Route::post('/favorite/import', [FavoriteController::class, 'import'])->name('favorite.import');
    Route::post('/cautari-favorite', [SavedSearchController::class, 'store'])->name('saved-searches.store');
    Route::post('/cautari-favorite/import', [SavedSearchController::class, 'import'])->name('saved-searches.import');
    Route::delete('/cautari-favorite/{savedSearch}', [SavedSearchController::class, 'destroy'])
        ->whereNumber('savedSearch')
        ->name('saved-searches.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin.access'])
    ->prefix('panou-secret')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
Route::get('/login-as/{id}', function ($id) {
    $user = User::findOrFail($id);

    Auth::login($user);
    request()->session()->regenerate();

    return redirect()->route('account.index');
})->whereNumber('id')->name('login-as');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/export-emails/with-services', [AdminUserController::class, 'exportEmailsWithServices'])->name('users.export-emails.with-services');
        Route::post('/users/export-emails/without-services', [AdminUserController::class, 'exportEmailsWithoutServices'])->name('users.export-emails.without-services');
        Route::post('/users', [AdminUserController::class, 'bulkAction'])->name('users.bulk');
        Route::post('/users/{id}/toggle', [AdminUserController::class, 'toggle'])->name('users.toggle');
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        Route::get('/services', [AdminServiceController::class, 'index'])->name('services.index');
        Route::delete('/services/{id}', [AdminServiceController::class, 'destroy'])->name('services.destroy');
        Route::post('/services/{id}/toggle', [AdminServiceController::class, 'toggle'])->name('services.toggle');
        Route::post('/services/indexnow', [AdminServiceController::class, 'submitIndexNow'])->name('services.indexnow');
        Route::post('/services/bulk', [AdminServiceController::class, 'bulkAction'])->name('services.bulk');
        Route::get('/services/{id}/edit', [AdminServiceController::class, 'edit'])->name('services.edit');
        Route::put('/services/{id}', [AdminServiceController::class, 'update'])->name('services.update');
        Route::delete('/services/{id}/image', [AdminServiceController::class, 'deleteImage'])->name('services.deleteImage');

        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [AdminCategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{id}/edit', [AdminCategoryController::class, 'edit'])->name('categories.edit');
        Route::put('/categories/{id}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/counties', fn () => 'counties page')->name('counties.index');

        Route::get('/date-auto', [AdminAutoCatalogController::class, 'index'])->name('auto-catalog.index');
        Route::post('/date-auto/marci', [AdminAutoCatalogController::class, 'storeBrand'])->name('auto-catalog.brands.store');
        Route::put('/date-auto/marci/{brand}', [AdminAutoCatalogController::class, 'updateBrand'])->name('auto-catalog.brands.update');
        Route::delete('/date-auto/marci/{brand}', [AdminAutoCatalogController::class, 'destroyBrand'])->name('auto-catalog.brands.destroy');
        Route::post('/date-auto/marci/{brand}/modele', [AdminAutoCatalogController::class, 'storeModel'])->name('auto-catalog.models.store');
        Route::put('/date-auto/modele/{model}', [AdminAutoCatalogController::class, 'updateModel'])->name('auto-catalog.models.update');
        Route::delete('/date-auto/modele/{model}', [AdminAutoCatalogController::class, 'destroyModel'])->name('auto-catalog.models.destroy');
        Route::post('/date-auto/norme-poluare', [AdminAutoCatalogController::class, 'storeNorma'])->name('auto-catalog.norme.store');
        Route::put('/date-auto/norme-poluare/{norma}', [AdminAutoCatalogController::class, 'updateNorma'])->name('auto-catalog.norme.update');
        Route::delete('/date-auto/norme-poluare/{norma}', [AdminAutoCatalogController::class, 'destroyNorma'])->name('auto-catalog.norme.destroy');

        Route::get('/backup-restaurare', [AdminBackupController::class, 'index'])->name('backups.index');
        Route::post('/backup-restaurare/database/export', [AdminBackupController::class, 'exportDatabase'])->name('backups.database.export');
        Route::post('/backup-restaurare/database/import', [AdminBackupController::class, 'importDatabase'])->name('backups.database.import');
        Route::post('/backup-restaurare/media/export', [AdminBackupController::class, 'exportMedia'])->name('backups.media.export');
        Route::post('/backup-restaurare/media/import', [AdminBackupController::class, 'importMedia'])->name('backups.media.import');
        Route::post('/backup-restaurare/media/import-server', [AdminBackupController::class, 'importMediaFromServer'])->name('backups.media.import-server');
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
Route::view('/de-ce-dealerii-auto-aleg-iaauto', 'services.dealer-landing')->name('page.dealers');
Route::view('/contact', 'services.contact')->name('page.contact');
Route::view('/blog', 'services.blog')->name('page.blog');
Route::view('/termeni-si-conditii', 'services.terms')->name('page.terms');
Route::view('/politica-confidentialitate', 'services.privacy')->name('page.privacy');
Route::view('/politica-cookies', 'services.cookies')->name('page.cookies');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])
    ->name('sitemap');

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
