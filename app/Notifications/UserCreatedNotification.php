<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class UserCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The created user's password.
     * @var string
     */
    public string $password;

    /**
     * The authenticated user.
     *
     * @var Authenticatable
     */
    public Authenticatable $user;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
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
            ->subject(Lang::get('Account created'))
            ->line(Lang::get('Welcome, we are glad to have you on board!'))
            ->line(Lang::get('You can log in by clicking the button below.'))
            ->action(Lang::get('login'), config('app.frontend_url') . "/")
            ->line(Lang::get('Password: :password', ['password' => $this->password]));
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
