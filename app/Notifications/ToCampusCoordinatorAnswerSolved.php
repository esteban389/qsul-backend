<?php

namespace App\Notifications;

use App\Models\Answer;
use App\Models\Observation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ToCampusCoordinatorAnswerSolved extends Notification implements ShouldQueue
{
    use Queueable;

    public Answer $answer;
    public Observation $observation;

    /**
     * Create a new notification instance.
     */
    public function __construct(Answer $answer, Observation $observation)
    {
        $this->answer = $answer;
        $this->observation = $observation;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $service = $this->answer->employeeService->service->name;
        $employeeName = $this->answer->employeeService->employee->name;
        $date = $this->answer->created_at->format('d/m/Y');
        $processLeader = $this->observation->user->name;

        return (new MailMessage)
            ->subject('Un comentario ha sido atendido')
            ->greeting('Hola ' . $notifiable->name . ',')
            ->line("Se ha resuelto un comentario relacionado con el servicio **{$service}**.")
            ->line("Empleado involucrado: {$employeeName}")
            ->line("Fecha del comentario: {$date}")
            ->line("Observación del jefe de departamento ({$processLeader}):")
            ->line("\"{$this->observation->description}\"")
            ->line('Gracias por estar al pendiente del seguimiento.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $service = $this->answer->employeeService->service;
        $employeeName = $this->answer->employeeService->employee->name;
        $date = $this->answer->created_at->format('d/m/Y');
        $processLeader = $this->observation->user->name;
        return [
            'title' => 'Un comentario ha sido atendido para el servicio ' . $service->name . ' prestado por el empleado ' . $employeeName
                . ' el día ' . $date . ' por ' . $processLeader,
            'type' => 'info',
            'date' => $this->answer->solved_at,
        ];
    }
}
