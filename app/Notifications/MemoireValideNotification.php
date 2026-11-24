<?php

namespace App\Notifications;

use App\Models\Memoire;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemoireValideNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Memoire $memoire) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre mémoire a été validé')
            ->greeting("Bonjour {$notifiable->prenom},")
            ->line("Votre mémoire « {$this->memoire->titre} » a été validé et publié dans le catalogue.");
    }
}
