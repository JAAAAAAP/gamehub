<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;

class UserActivityLogged
{
    use SerializesModels;

    public $user_id;
    public $log_type;
    public $activity;
    public $detail;
    /**
     * Create a new event instance.
     */
    public function __construct(int $user_id,string $log_type, string $activity, ?string $detail = null)
    {
        $this->user_id = $user_id;
        $this->log_type = $log_type;
        $this->activity = $activity;
        $this->detail = $detail;
    }
}
