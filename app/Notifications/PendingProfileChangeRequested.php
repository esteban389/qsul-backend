<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\PendingProfileChange;

class PendingProfileChangeRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public PendingProfileChange $pendingChange) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Solicitud de Cambio de Perfil')
            ->line('Se ha solicitado un cambio de perfil, requiere su atención.')
            ->action('Ver Solicitud', config('app.frontend_url') . '/perfil?tab=solicitud-cambios');
    }

    public function toArray($notifiable)
    {
        return [
            'pending_profile_change_id' => $this->pendingChange->id,
            'type' => $this->pendingChange->change_type,
            'user_id' => $this->pendingChange->user_id,
            'requested_by' => $this->pendingChange->requested_by,
        ];
    }
}
