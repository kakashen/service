<?php

namespace App\Http\Controllers;

use App\Model\Communication;
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

    public function send(Request $request)
    {
        $user = Auth::user();
        $from = $user->id; // 发送人id
        $to = $request->get('to'); // 接收人id
        $content = $request->get('content');
        $direction = $request->get('direction'); // 1客户发给客服, 2客服发给客户
        $type = $request->get('type', 1);
        $communication_id = $request->get('communication_id');

        if (!isset($to)) {
            return response()->json(['message' => '接收人不能为空', 'code' => 0]);
        }
        if (!isset($content)) {
            return response()->json(['message' => '内容不能为空', 'code' => 0]);
        }
        if (!isset($direction)) {
            return response()->json(['message' => '方向不能为空', 'code' => 0]);
        }

        $comm = new Communication();

        if (!isset($communication_id)) {
            $communication_id = $comm->insertGetId([
                'client_id' => $from,
                'staff_id' => $to,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),

            ]);
            if (!$communication_id) {
                return response()->json(['message' => '创建会话失败', 'code' => 0]);
            }
        }

        $communication = $comm->where('status', 1)->find($communication_id);

        if (!$communication) {
            return response()->json(['message' => '当前会话已关闭', 'code' => 0]);
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

    public function getAll(Request $request)
    {
        $communication_id = $request->get('communication_id');

        if (!isset($communication_id)) {
            return response()->json(['message' => '会话不能为空', 'code' => 0]);
        }


        $data = $this->message
            ->where('communication_id', $communication_id)
            ->get();

        return response()->json(['message' => '获取消息成功', 'code' => 200, 'data' => $data]);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 客户发送消息
     */
    public function cSend(Request $request)
    {
        $client_id = $request->get('client_id');
        $staff_id = $request->get('staff_id');
        $content = $request->get('content');
        $type = $request->get('type');
        $communication_id = $request->get('communication_id');

        if (!isset($client_id)) {
            return response()->json(['message' => '发送人不能为空', 'code' => 0]);
        }
        if (!isset($staff_id)) {
            return response()->json(['message' => '接收人不能为空', 'code' => 0]);
        }
        if (!isset($content)) {
            return response()->json(['message' => '消息内容不能为空', 'code' => 0]);
        }
        if (!isset($type)) {
            return response()->json(['message' => '消息类型不能为空', 'code' => 0]);
        }

        $comm = new Communication();

        $communication = $comm->where('status', 1)->find($communication_id);

        if (!isset($communication_id) || !$communication) {
            $communication_id = $comm->insertGetId([
                'client_id' => $client_id,
                'staff_id' => $staff_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),

            ]);
            if (!$communication_id) {
                return response()->json(['message' => '创建会话失败', 'code' => 0]);
            }
            return response()->json(['message' => '当前会话已关闭', 'code' => 0]);
        }

        $message = $this->message;
        $message->client_id = $client_id;
        $message->staff_id = $staff_id;
        $message->content = $content;
        $message->direction = 1;
        $message->type = $type;
        $message->communication_id = $communication_id;
        $ret = $message->save();
        if ($ret) {
            return response()->json(['message' => '发送成功', 'code' => 200]);

        }
        return response()->json(['message' => '发送失败', 'code' => 0]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 客户获取消息
     */
    public function cGet(Request $request)
    {
        $client_id = $request->get('client_id');
        if (!isset($client_id)) {
            return response()->json(['message' => '客户id不能为空', 'code' => 0]);
        }

        $data = $this->message::where('client_id', $client_id)->get();
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $data]);
    }

}
