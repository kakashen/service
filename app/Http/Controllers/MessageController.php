<?php

namespace App\Http\Controllers;

use App\Model\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    private $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function sendMessage(Request $request)
    {
        $user = Auth::user();
        $from = $user->id; // 发送人id
        $to = $request->get('to'); // 接收人id
        $content = $request->get('content');
        $direction = $request->get('direction');
        $type = $request->get('type', 1);
        $communication_id = $request->get('communication_id', 1);

        if (!isset($to)) {
            return response()->json(['message' => '接收人不能为空', 'code' => 0]);
        }
        if (!isset($content)) {
            return response()->json(['message' => '内容不能为空', 'code' => 0]);
        }
        if (!isset($direction)) {
            return response()->json(['message' => '方向不能为空', 'code' => 0]);
        }

        $message = $this->message;
        $message->from = $from;
        $message->to = $to;
        $message->content = $content;
        $message->direction = $direction;
        $message->type = $type;
        $message->communication_id = $communication_id;
        $message->from = $from;
        $ret = $message->save();
        if ($ret) {
            return response()->json(['message' => '发送成功', 'code' => 200]);

        }
        return response()->json(['message' => '发送失败', 'code' => 0]);

    }

    public function getMessage(Request $request)
    {
        $user = Auth::user();

        $communication_id = $request->get('communication_id', 1);

        if (!isset($communication_id)) {
            return response()->json(['message' => '会话不能为空', 'code' => 0]);
        }


        $data = $this->message
            ->where('communication_id', $communication_id)
            ->get();

        return response()->json(['message' => '发送成功', 'code' => 200, 'data' => $data]);



    }

}
