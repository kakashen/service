<?php

namespace App\Http\Controllers;

use App\Model\ActiveChat;
use App\Model\Communication;
use App\Model\Message;
use App\Model\Staff;
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
        $staff_id = $user->id; // 发送人id
        $client_id = $request->get('client_id'); // 接收人id
        $content = $request->get('content');
        $type = $request->get('type');
        $direction = $request->get('direction');
        $communication_id = $request->get('communication_id');

        if (!isset($client_id)) {
            return response()->json(['message' => '客户id不能为空', 'code' => 0]);
        }
        if (!isset($content)) {
            return response()->json(['message' => '内容不能为空', 'code' => 0]);
        }
        if (!isset($direction)) {
            return response()->json(['message' => '方向不能为空', 'code' => 0]);
        }
        if (!isset($type)) {
            return response()->json(['message' => '类型不能为空', 'code' => 0]);
        }

        $comm = new Communication();

        if (!isset($communication_id)) {
            $communication_id = $comm->insertGetId([
                'client_id' => $client_id,
                'staff_id' => $staff_id,
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

        $time = date('Y-m-d H:i:s');
        $message_id = $message->insertGetId([
            'client_id' => $client_id,
            'staff_id' => $staff_id,
            'content' => $content,
            'direction' => $direction,
            'type' => $type,
            'communication_id' => $communication_id,
            'created_at' => $time,
            'updated_at' => $time,

        ]);
        if ($message_id) {
            return response()->json(['message' => '发送成功', 'code' => 200,
                'data' => ['id' => $message_id, 'created_at' => $time]]);

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
        if (!isset($staff_id)) {
            $ids = $this->getIdleStaffIds();
            $count = count($ids);
            if ($count == 0) {
                return response()->json([
                    'message' => '浣洗智能客服提醒您，人工服务前忙，请稍后再试，谢谢~',
                    'code' => 0
                ]);
            }
            $staff_id = $ids[rand(0, count($ids))];
        }

        $content = $request->get('content');
        $type = $request->get('type');
        $direction = $request->get('direction');
        $communication_id = $request->get('communication_id');

        if (!isset($client_id)) {
            return response()->json(['message' => '客户不能为空', 'code' => 0]);
        }
        if (!isset($staff_id)) {
            return response()->json(['message' => '客服不能为空', 'code' => 0]);
        }
        if (!isset($content)) {
            return response()->json(['message' => '消息内容不能为空', 'code' => 0]);
        }
        if (!isset($type)) {
            return response()->json(['message' => '消息类型不能为空', 'code' => 0]);
        }
        if (!isset($direction)) {
            return response()->json(['message' => '方向不能为空', 'code' => 0]);
        }

        if (!isset($communication_id)) {
            $communication_id = $this->getCommunicationId($client_id, $staff_id);
        }

        if ($communication_id) {
            $ret = Communication::where('status', 1)->find($communication_id);
            if (!$ret) {
                $communication_id = $this->getCommunicationId($client_id, $staff_id);
            }
        }

        $time = date('Y-m-d H:i:s');
        $message_id = $this->message->insertGetId([
            'client_id' => $client_id,
            'staff_id' => $staff_id,
            'content' => $content,
            'direction' => $direction,
            'type' => $type,
            'communication_id' => $communication_id,
            'created_at' => $time,
            'updated_at' => $time,
        ]);
        if ($message_id) {
            return response()->json(['message' => '发送成功', 'code' => 200,
                'data' => ['id' => $message_id, 'created_at' => $time]]);
        }
        return response()->json(['message' => '发送失败', 'code' => 0]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 客户根据会话id获取消息
     */
    public function cGet(Request $request)
    {
        $communication_id = $request->get('communication_id');
        if (!isset($communication_id)) {
            return response()->json(['message' => '会话id不能为空', 'code' => 0]);
        }

        $data = $this->message::where('communication_id', $communication_id)->get();
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $data]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 客户获取新消息
     */
    public function cGetNew(Request $request)
    {
        $message_id = $request->get('message_id');
        if (!isset($message_id)) {
            return response()->json(['message' => '消息id不能为空', 'code' => 0]);
        }
        $client_id = $request->get('client_id');
        if (!isset($client_id)) {
            return response()->json(['message' => '客户id不能为空', 'code' => 0]);
        }

        $query = $this->message::where('id', '>', $message_id)
            ->where('client_id', $client_id)
            ->where('direction', 2);
        $list = $query->get();
        $max_unread = $query->where('is_read', 0)->first();

        $data = [
            'list' => $list,
            'max_unread' => $max_unread['id']
        ];
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $data]);


    }

    public function getNew(Request $request)
    {
        $staff_id = Auth::user()->id;
        $list = [];
        $ids = ActiveChat::select('client_id')->where('staff_id', $staff_id)->get()->toArray();
        $client_ids = [];
        $data = $request->get('data');
        foreach ($data as $datum) {
            if (!isset($datum['client_id'], $datum['message_id'])) continue;
            $query = $this->message->where('id', '>', $datum['message_id'])
                ->where('client_id', $datum['client_id'])
                ->where('staff_id', $staff_id)
                ->where('direction', 1);
            $messages = $query->get();
            $client_ids[] = $datum['client_id'];

            $max_unread = $query->where('is_read', 0)->select('id')->first();

            $list[] = [
                'list' => $messages,
                'max_unread' => $max_unread['id']
            ];
        }

        $diffs = array_diff(array_column($ids, 'client_id'), $client_ids);

        foreach ($diffs as $diff) {
            $query = $diff_msg = $this->message
                ->where('client_id', $diff)
                ->where('staff_id', $staff_id)
                ->where('direction', 1);
            $diff_msg = $query->get();

            $max_unread = $query->where('is_read', 0)->select('id')->first();
            $list[] = [
                'list' => $diff_msg,
                'max_unread' => $max_unread['id']
            ];
        }
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $list]);
    }

    private function getIdleStaffIds(): array
    {
        return Staff::select('id')->where('status', 1)->get()->toArray();
    }

    private function getCommunicationId($client_id, $staff_id)
    {
        $comm = new Communication();
        $communication_id = $comm->insertGetId([
            'client_id' => $client_id,
            'staff_id' => $staff_id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return $communication_id;
    }
}
