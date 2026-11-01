<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Development-only (C22). Sent immediately, not queued, so the code reaches
 * the test inbox before it changes.
 */
class DevelopmentTwoFactorCode extends Notification
{
    public function __construct(public readonly string $code) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('Your two-factor code (development)'))
            ->line(__('Your current two-factor code is:'))
            ->line('**'.$this->code.'**')
            ->line(__('It changes every 30 seconds. If it no longer works, ask for a new one.'))
            ->line(__('Development only: campus servers never send two-factor codes by email.'));
    }
}
