<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountCreated extends Notification
{
    public function __construct(private readonly string $token)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->email,
        ], false));

        return (new MailMessage)
            ->subject('Your '.config('app.name').' account is ready')
            ->greeting('Welcome, '.$notifiable->name.'!')
            ->line('An account has been created for you at '.config('app.name').' as a '.$notifiable->role.'.')
            ->line('Your login email is: '.$notifiable->email)
            ->action('Set your password', $url)
            ->line('This link will expire in '.config('auth.passwords.users.expire', 60).' minutes. If it expires, use the "Forgot your password" link on the login page to request a new one.');
    }
}
