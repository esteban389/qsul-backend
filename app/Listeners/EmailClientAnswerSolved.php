<?php

namespace App\Listeners;

use App\Events\AnswerSolved;
use App\Mail\ToClientAnswerSolved as AnswerSolvedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class EmailClientAnswerSolved implements ShouldQueue
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
    public function handle(AnswerSolved $event): void
    {
        $email = $event->answer->email;
        Mail::to($email)->send(new AnswerSolvedMail($event->answer));
    }
}
