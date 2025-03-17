<?php
// App\Notifications\ResetPasswordNotification.php
namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = config('app.frontend_url') . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->email);
        
        return (new MailMessage)
            ->subject(Lang::get('Réinitialisation de mot de passe'))
            ->line(Lang::get('Vous recevez cet email car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.'))
            ->action(Lang::get('Réinitialiser le mot de passe'), $url)
            ->line(Lang::get('Ce lien expirera dans :count minutes.', ['count' => config('auth.passwords.users.expire')]))
            ->line(Lang::get('Si vous n\'avez pas demandé de réinitialisation de mot de passe, aucune action n\'est requise.'));
    }
}