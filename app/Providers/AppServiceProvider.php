<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

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
        // Customizăm emailul de resetare parolă
        ResetPassword::toMailUsing(function ($notifiable, $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Resetare parolă MeseriasBun.ro')
                ->greeting('Salut!')
                ->line('Ai cerut resetarea parolei pentru contul tău MeseriasBun.ro.')
                ->action('Resetează parola', $url)
                ->line('Dacă nu ai cerut resetarea, poți ignora acest email.');
        });
    }
}
