<?php

namespace Tests\Unit;

use App\Models\Service;
use App\Models\User;
use App\Notifications\ServicePublishedConfirmation;
use Tests\TestCase;

class ServicePublishedConfirmationTest extends TestCase
{
    public function test_confirmation_email_uses_account_listing_details(): void
    {
        $user = new User([
            'name' => 'Ion Popescu',
            'email' => 'ion@example.com',
        ]);
        $service = new Service([
            'title' => 'BMW 320d de vanzare',
            'brand' => 'BMW',
            'model' => '320d',
        ]);

        $notification = new ServicePublishedConfirmation($service);
        $mail = $notification->toMail($user);

        $this->assertSame(['mail'], $notification->via($user));
        $this->assertSame('Anunțul tău a fost publicat', $mail->subject);
        $this->assertSame([
            'html' => 'emails.service-published',
            'text' => 'emails.service-published-text',
        ], $mail->view);
        $this->assertSame($user, $mail->viewData['user']);
        $this->assertSame($service, $mail->viewData['service']);
        $this->assertSame('BMW 320d', $mail->viewData['listingLabel']);
        $this->assertStringContainsString('/contul-meu?tab=anunturi', $mail->viewData['accountUrl']);
    }
}
