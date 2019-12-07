<?php

namespace App\Events;

use App\Model\Communication;
use Illuminate\Queue\SerializesModels;

class CommunicationEvent extends Event
{
    use SerializesModels;
    public $communication;

    public function __construct(Communication $communication)
    {
        $this->communication = $communication;
    }
}
