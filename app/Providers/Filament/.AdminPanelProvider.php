<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Illuminate\Support\Facades\Gate;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('administrator')
            ->login()
            ->authGuard('web')
            ->middleware([
                'auth',
                'admin.only',
            ]);
    }

    public function boot()
    {
        // Middleware pentru acces
        app('router')->aliasMiddleware('admin.only', \App\Http\Middleware\AdminOnly::class);
    }
}
