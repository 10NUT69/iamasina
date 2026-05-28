<?php

namespace App\Providers;

use App\Models\Service;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Password::defaults(function () {
            return Password::min(6);
        });

        View::composer('admin.*', function ($view) {
            $view->with('adminSidebarStats', Cache::remember('admin.sidebar.stats', now()->addMinute(), function () {
                $todayStart = now()->startOfDay();
                $todayEnd = now()->endOfDay();

                return [
                    'pending_services' => Service::whereNull('deleted_at')->where('status', 'pending')->count(),
                    'active_services' => Service::whereNull('deleted_at')->where('status', 'active')->count(),
                    'total_services' => Service::whereNull('deleted_at')->count(),
                    'new_services_today' => Service::whereNull('deleted_at')->whereBetween('published_at', [$todayStart, $todayEnd])->count(),
                    'total_users' => User::count(),
                    'new_users_today' => User::whereBetween('created_at', [$todayStart, $todayEnd])->count(),
                ];
            }));
        });

        // Customizăm emailul de resetare parolă
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Resetare parolă iaAuto.ro')
                ->view([
                    'html' => 'emails.password-reset',
                    'text' => 'emails.password-reset-text',
                ], [
                    'url' => $url,
                ]);
        });
    }
}
