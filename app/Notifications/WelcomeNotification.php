<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenue sur la bibliothèque numérique des mémoires ENEAM')
            ->greeting("Bonjour {$notifiable->prenom},")
            ->line('Votre compte a été créé avec succès.')
            ->line("Matricule : {$notifiable->matricule}")
            ->line('Connectez-vous avec votre matricule ; un code de vérification vous sera envoyé par e-mail à chaque connexion.');
    }
}