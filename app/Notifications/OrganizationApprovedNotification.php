<?php

namespace App\Notifications;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrganizationApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Organization $organization
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Your organization access has been approved')
            ->greeting("Hello {$notifiable->name},")
            ->line('Your organization request has been approved.')
            ->line("Organization: {$this->organization->name}")
            ->line('You can now access organization features in the dashboard.');
    }
}
