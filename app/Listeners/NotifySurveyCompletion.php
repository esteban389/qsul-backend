<?php

namespace App\Listeners;

use App\DTOs\Auth\UserRole;
use App\Events\SurveyCompletion;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use App\Notifications\SurveyAnsweredNotification;

class NotifySurveyCompletion implements ShouldQueueAfterCommit
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
     * Notifies the: 
     * - National Coordinator, 
     * - Campus Coordinator from the campus 
     * where the survey was taken, and 
     * - Process Leader from the campus where
     * the survey was taken and the service's
     * process evaluated.
     */
    public function handle(SurveyCompletion $event): void
    {
        $nationalCoordinator = User::where('role', UserRole::NationalCoordinator)->first();
        $employeeService = $event->answer->employeeService()->with('employee')->first();
        $employee = $employeeService->employee;
        $campus_id = $employee->campus_id;
        $campusCoordinator = User::where('role', UserRole::CampusCoordinator)
            ->with('employee')
            ->where('campus_id', $campus_id)
            ->first();
        // TODO check for the process too
        $processLeader = User::where('role', UserRole::ProcessLeader)
            ->with('employee')
            ->where('campus_id', $campus_id)
            ->whereHas('employee', function ($query) use ($employee) {
                $query->where('process_id', $employee->process_id);
            })
            ->first();
        if ($nationalCoordinator) {
            // Notify the National Coordinator
            $nationalCoordinator->notify(new SurveyAnsweredNotification($event->answer));
        }
        if ($campusCoordinator) {
            // Notify the Campus Coordinator
            $campusCoordinator->notify(new SurveyAnsweredNotification($event->answer));
        }
        if ($processLeader) {
            // Notify the Process Leader
            $processLeader->notify(new SurveyAnsweredNotification($event->answer));
        }
    }
}
