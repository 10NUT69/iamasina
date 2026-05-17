<?php

namespace App\Notifications;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ServicePublishedConfirmation extends Notification
{
    use Queueable;

    public function __construct(private readonly Service $service)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Confirmare publicare anunț iaAuto.ro')
            ->view([
                'html' => 'emails.service-published',
                'text' => 'emails.service-published-text',
            ], [
                'user' => $notifiable,
                'service' => $this->service,
                'accountUrl' => route('account.index') . '?tab=anunturi',
            ]);
    }
}
