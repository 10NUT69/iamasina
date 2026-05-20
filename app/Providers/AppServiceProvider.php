<?php

namespace App\Providers;

use App\Models\Service;
use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\View;

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
        View::composer('admin.*', function ($view) {
            $view->with('adminSidebarStats', [
                'pending_services' => Service::whereNull('deleted_at')->where('status', 'pending')->count(),
                'active_services' => Service::whereNull('deleted_at')->where('status', 'active')->count(),
                'total_services' => Service::whereNull('deleted_at')->count(),
                'new_services_today' => Service::whereNull('deleted_at')->whereDate('published_at', now()->toDateString())->count(),
                'total_users' => User::count(),
                'new_users_today' => User::whereDate('created_at', now()->toDateString())->count(),
            ]);
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
