<?php

namespace App\Listeners;

use App\Notifications\PasswordResetNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Notification;

class NotifyPasswordReset
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PasswordReset $event): void
    {
        Notification::send($event->user, new PasswordResetNotification());
    }
}
