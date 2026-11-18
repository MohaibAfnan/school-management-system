<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailOtpNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $code) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verify your school account')
            ->greeting('Verify your email')
            ->line('Use this one-time code to verify your school owner account:')
            ->line($this->code)
            ->line('This code expires in 10 minutes. If you did not request it, you can ignore this email.');
    }
}
