<?php

namespace App\Listeners;

use App\Events\AnswerSolved;
use App\Notifications\ToCampusCoordinatorAnswerSolved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyCampusCoordinatorAnswerSolved implements ShouldQueue
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
        $campusCoordinator = $event->answer->employeeService->employee->campus->campusCoordinator();
        $campusCoordinator->notify(new ToCampusCoordinatorAnswerSolved($event->answer, $event->observation));
    }
}
