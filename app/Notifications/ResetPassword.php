<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPassword extends Notification
{
    use Queueable;
    public $token;
    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $token = $this->token;
        return (new MailMessage)
                    ->greeting('Hello!')
                    ->line('Anda menerima email ini karena kami menerima permintaan reset password untuk akun anda.')
                    ->action('Reset Password', "http://localhost:5173/password/{$token}?email={$notifiable->getEmailForPasswordReset()}")
                    ->line('Link reset password akan kadaluarsa dalam 60 menit.')
                    ->line('Jika anda tidak merasa melakukan permintaan reset password, abaikan email ini.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
