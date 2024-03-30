<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerify extends Notification
{
    use Queueable;

    public $details;

    /**
     * Create a new notification instance.
     */
    public function __construct($details)
    {
        $this->details = $details;
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
        return (new MailMessage)
            ->subject('Account Verification from Atma Bakery')
            ->greeting('Hello ' . $this->details['nama'] . '!')
            ->line('Anda telah melakukan registrasi akun dengan menggunakan email ini.')
            ->line('Berikut adalah data anda :')
            ->line('Email : ' . $this->details['email'])
            ->line('Website : ' . $this->details['website'])
            ->line('Tanggal Register : ' . $this->details['datetime'] . ' WIB')
            ->line('Buka Link dibawah untuk melakukan verifikasi akun')
            ->action('Verifikasi Akun', $this->details['url'])
            ->line('Terima kasih telah melakukan registrasi.');
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

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
