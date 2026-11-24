<?php

namespace App\Notifications;

use App\Models\Memoire;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MemoireRejeteNotification extends Notification
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
            ->subject('Votre mémoire a été rejeté')
            ->greeting("Bonjour {$notifiable->prenom},")
            ->line("Votre mémoire « {$this->memoire->titre} » a été rejeté.")
            ->line("Motif : {$this->memoire->motif_rejet}")
            ->line('Vous pouvez corriger et déposer une nouvelle version.');
    }
}
