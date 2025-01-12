<?php

namespace App\Notifications;

use App\Models\Answer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SurveyAnsweredNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(private Answer $answer)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('The introduction to the notification.')
            ->action('Notification Action', url('/'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        if ($this->answer->average < 3) {
            return [
                'title' => 'Evaluación insatisfactoria',
                'message' => 'Se ha evalueado con insatisfacción al empleado ' . $this->answer->employeeService->employee->name . ', mientras prestaba el servicio "' . $this->answer->employeeService->service->name . '". La calificación promedio fue de ' . $this->answer->average,
                'answer_id' => $this->answer->id,
                'type' => 'danger',
                'date' => $this->answer->created_at
            ];
        }
        if ($this->answer->average > 4) {
            return [
                'title' => 'Evaluación satisfactoria',
                'message' => 'Se ha evalueado con gran satisfacción al empleado ' . $this->answer->employeeService->employee->name . ', mientras prestaba el servicio "' . $this->answer->employeeService->service->name . '". La calificación promedio fue de ' . $this->answer->average,
                'answer_id' => $this->answer->id,
                'type' => 'success',
                'date' => $this->answer->created_at
            ];
        }
        return [
            'title' => 'Evaluación completada',
            'message' => 'Se ha evalueado al empleado ' . $this->answer->employeeService->employee->name . ', mientras prestaba el servicio "' . $this->answer->employeeService->service->name . '". La calificación promedio fue de ' . $this->answer->average,
            'answer_id' => $this->answer->id,
            'type' => 'info',
            'date' => $this->answer->created_at
        ];
    }
}
