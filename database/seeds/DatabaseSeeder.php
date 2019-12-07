<?php

use App\Model\Communication;
use App\Model\Message;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call('UsersTableSeeder');
        for ($i = 1; $i < 100; $i++) {
            $comm = new Communication();
            $comm->client_id = $i;
            $comm->staff_id = rand(1, 5);
            $comm->status = 0;
            $comm->save();
            echo "i -- " . $i . '\n';
        }

        for ($j = 1; $j<10000; $j++) {
            $msg = new Message();
            $msg->client_id = rand(1,10);
            $msg->staff_id = rand(1,5);
            $msg->content = "无敌是多么的寂寞" . $j;
            $msg->direction = rand(1,2);
            $msg->type = 1;
            $msg->communication_id = rand(1,100);
            $msg->is_read = 1;
            $msg->save();
            echo "j -- " . $j . '\n';
        }
    }
}
