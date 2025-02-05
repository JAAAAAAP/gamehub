<?php

namespace App\Listeners;

use App\Events\UserActivityLogged;
use App\Models\UserActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogUserActivity
{

    /**
     * Handle the event.
     */
    public function handle(UserActivityLogged $event): void
    {
        UserActivityLog::create([
            'user_id' => $event->user_id,
            'log_type' => $event->log_type,
            'activity' => $event->activity,
            'detail' => $event->detail,
        ]);
    }
}
