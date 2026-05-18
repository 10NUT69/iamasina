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
            ->subject('Anunțul tău a fost publicat')
            ->view([
                'html' => 'emails.service-published',
                'text' => 'emails.service-published-text',
            ], [
                'user' => $notifiable,
                'service' => $this->service,
                'listingLabel' => $this->listingLabel(),
                'accountUrl' => route('account.index') . '?tab=anunturi',
            ]);
    }

    private function listingLabel(): string
    {
        $brand = $this->service->brandRel?->name ?: $this->service->brand;
        $model = $this->service->modelRel?->name ?: $this->service->model;
        $label = trim(collect([$brand, $model])->filter()->implode(' '));

        return $label !== '' ? $label : $this->service->title;
    }
}
