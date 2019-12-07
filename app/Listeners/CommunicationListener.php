<?php

namespace App\Listeners;

use App\Events\CommunicationEvent;
use App\Model\Communication;
use App\Model\Message;

class CommunicationListener
{
    public function handle(CommunicationEvent $event)
    {
        $communication = $event->communication;
        // $client_id = $communication->client_id;
        $staff_id = $communication->staff_id;
        // $communication_id = $communication->id;
        $comms = Communication::where('staff_id', $staff_id)
            ->where('status', 1)->get();

        foreach ($comms as $comm) {
            // $client_id = $comm->client_id;
            $communication_id = $comm->id;
            $msg = Message::where('communication_id', $communication_id)
                ->where('direction', 1)
                ->orderBy('id', 'desc')
                ->first();
            if (!$msg) continue;
            $created_at = $msg->created_at;
            if (time() - strtotime($created_at) < 300) continue;
            Communication::where('id', $communication_id)->update([
               'status' => 0
            ]);
        }
    }
}
