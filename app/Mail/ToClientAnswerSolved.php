<?php

namespace App\Mail;

use App\Models\Answer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;

class ToClientAnswerSolved extends Mailable
{
    use Queueable, SerializesModels;

    private Answer $answer;

    /**
     * Create a new message instance.
     */
    public function __construct(Answer $answer)
    {
        $this->answer = $answer;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu retroalimentación ha sido revisada',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $answer = $this->answer;
        $date = $answer->created_at->format('d/m/Y');
        $employeeService = $answer->employeeService()->with(['employee','service'])->first();
        $employee = $employeeService->employee->name;
        $service = $employeeService->service->name;

        $html = (new MailMessage)
            ->line("Gracias por tomarte el tiempo de compartir tu experiencia con nosotros el día {$date}, relacionada con el servicio de {$service} y la atención de {$employee}.")
            ->line('Tu retroalimentación ha sido revisada por el área correspondiente y se han tomado las acciones necesarias.')
            ->line('Si deseas obtener más información, no dudes en contactar al líder de proceso.')
            ->line('Agradecemos tu participación, ya que nos ayuda a mejorar continuamente.')
            ->line('Gracias por tu tiempo.')
            ->render();

        return new Content(
            htmlString: $html,
        );

    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
