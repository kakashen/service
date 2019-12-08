<?php

namespace App\Listeners;

use App\Events\CommunicationEvent;
use App\Model\Communication;
use App\Model\Message;
use App\Model\Staff;
use Illuminate\Support\Facades\DB;

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
            $client_id = $comm->client_id;
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

            DB::table('staff_grades')->updateOrInsert([
                'client_id' => $client_id,
                'staff_id' => $staff_id,
                'communication_id' => $communication_id,
            ], [
                'grade' => 80,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $query = Staff::where('staff_id', $staff_id);
            $grade_num = $query->first()->grade_num;
            $grade_avg = $query->first()->grade_avg;

            $query->update([
                'grade_num' => $grade_num + 1,
                'grade_avg' => floor($grade_avg * $grade_num / ($grade_num + 1))
            ]);

        }
    }
}
