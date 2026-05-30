<?php

namespace App\Notifications;

use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MissingServiceImagesReminder extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Collection $services,
        private readonly Carbon $periodStart,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $serviceCount = $this->services->count();

        return (new MailMessage)
            ->subject($serviceCount === 1
                ? 'Adaugă poze la anunțul tău pe iaAuto.ro'
                : 'Adaugă poze la anunțurile tale pe iaAuto.ro')
            ->view([
                'html' => 'emails.missing-service-images-reminder',
                'text' => 'emails.missing-service-images-reminder-text',
            ], [
                'user' => $notifiable,
                'services' => $this->services,
                'serviceCount' => $serviceCount,
                'listingItems' => $this->listingItems(),
                'accountUrl' => route('account.index', ['tab' => 'anunturi']),
                'periodLabel' => $this->periodStart->format('d.m.Y'),
            ]);
    }

    public function serviceIds(): array
    {
        return $this->services->pluck('id')->values()->all();
    }

    private function listingItems(): array
    {
        return $this->services
            ->map(fn (Service $service): array => [
                'label' => $this->listingLabel($service),
                'editUrl' => route('services.edit', $service->id),
                'publishedAt' => $service->published_at?->format('d.m.Y H:i'),
            ])
            ->values()
            ->all();
    }

    private function listingLabel(Service $service): string
    {
        $brand = $service->brandRel?->name ?: $service->brand;
        $model = $service->modelRel?->name ?: $service->model;
        $label = trim(collect([$brand, $model])->filter()->implode(' '));

        return $label !== '' ? $label : $service->title;
    }
}
