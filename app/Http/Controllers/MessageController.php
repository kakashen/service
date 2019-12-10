<?php

namespace App\Http\Controllers;

use App\Events\CommunicationEvent;
use App\Model\ActiveChat;
use App\Model\Communication;
use App\Model\Message;
use App\Model\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    private $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 客服发送消息
     */
    public function send(Request $request)
    {
        $user = Auth::user();
        $staff_id = $user->id; // 发送人id
        $client_id = $request->get('client_id'); // 接收人id
        $content = $request->get('content');
        $type = $request->get('type');
        $direction = $request->get('direction');

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

        $communication = Communication::where('client_id', $client_id)->orderBy('id', 'desc')->first();

        if (!$communication) {
            return response()->json(['message' => '该用户无会话', 'code' => 0]);
        }

        if ($communication->status == 0) {
            return response()->json(['message' => '该用户会话已关闭', 'code' => 0]);
        }

        $message = $this->message;

        $time = date('Y-m-d H:i:s');
        $message_id = $message->insertGetId([
            'client_id' => $client_id,
            'staff_id' => $staff_id,
            'content' => $content,
            'direction' => $direction,
            'type' => $type,
            'communication_id' => $communication->id,
            'created_at' => $time,
            'updated_at' => $time,

        ]);
        if ($message_id) {
            return response()->json(['message' => '发送成功', 'code' => 200,
                'data' => [
                    'id' => $message_id,
                    'communication_id' => $communication->id,
                    'client_id' => $client_id,
                    'created_at' => $time
                ]]);

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
        if (empty($staff_id)) {
            $ids = array_column($this->getIdleStaffIds(), 'id');
            $count = count($ids);
            if ($count == 0) {
                return response()->json([
                    'message' => '浣洗智能客服提醒您，人工服务前忙，请稍后再试，谢谢~',
                    'code' => 0
                ]);
            }

            $staff_id = $ids[rand(0, $count - 1)];
        }

        $content = $request->get('content');
        $type = $request->get('type');
        $direction = $request->get('direction');

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

        $communication_id = Communication::select('id')->where('status', 1)
                ->where('client_id', $client_id)->orderBy('id', 'desc')->first()->id ?? null;

        if (!$communication_id) {
            $communication_id = $this->getCommunicationId($client_id, $staff_id);
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
                'data' => [
                    'id' => $message_id,
                    'communication_id' => $communication_id,
                    'staff_id' => $staff_id,
                    'created_at' => $time
                ]]);
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

        $query = $this->message->where('client_id', $client_id);
        $read_query = clone $query;

        $list = $query->where('id', '>', $message_id)->where('direction', 2)->get();
        $max_read = $read_query->where('direction', 1)->where('is_read', 1)->orderBy('id', 'desc')->first();

        $data = [
            'list' => $list,
            'max_read' => $max_read['id']
        ];
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $data]);


    }

    public function getNew(Request $request)
    {
        $staff_id = Auth::user()->id;

        $comm = new Communication();
        $comm->staff_id = $staff_id;
        event(new CommunicationEvent($comm));

        $list = [];
        $ids = ActiveChat::select('client_id')->where('staff_id', $staff_id)->get()->toArray();
        $client_ids = [];
        $data = $request->get('data');
        $data = json_decode($data, true);
        foreach ($data as $datum) {
            if (!isset($datum['client_id'], $datum['message_id'])) continue;
            $query = $this->message->where('client_id', $datum['client_id'])
                ->where('staff_id', $staff_id);
            $read_query = clone $query;

            $messages = $query->where('id', '>', $datum['message_id'])->where('direction', 1)->get();
            $client_ids[] = $datum['client_id'];

            $max_read = $read_query->where('direction', 2)->where('is_read', 1)->select('id')->orderBy('id', 'desc')->first();

            $list[] = [
                'list' => $messages,
                'max_read' => $max_read['id'],
                'client_id' => $datum['client_id']
            ];
        }

        $diffs = array_diff(array_column($ids, 'client_id'), $client_ids);

        foreach ($diffs as $diff) {
            $query = $diff_msg = $this->message
                ->where('client_id', $diff)
                ->where('staff_id', $staff_id);

            $read_query = clone $query;

            $diff_msg = $query->where('direction', 1)->get();

            $max_read = $read_query->where('direction', 2)->where('is_read', 1)->select('id')->orderBy('id', 'desc')->first();
            $list[] = [
                'list' => $diff_msg,
                'max_read' => $max_read['id'],
                'client_id' => $diff
            ];
        }
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $list]);
    }

    private function getIdleStaffIds(): array
    {
        return Staff::select('id')->where('status', 1)
            ->where('active', 1)->where('username', '!=', 'admin')
            ->get()->toArray();
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

    public function isRead(Request $request)
    {
        $communication_id = $request->get('communication_id');
        if (!isset($communication_id)) {
            return response()->json(['message' => '会话id不能为空', 'code' => 0]);
        }
        if ($communication_id == "false" || $communication_id == false || $communication_id == null) {
            $communication_id = 0;
        }

        $client_id = $request->get('client_id');

        $query = $this->message->where('communication_id', $communication_id);

        if ($client_id) {
            $query->where('client_id', $client_id);
        } else {
            $query->where('staff_id', Auth::user()->id ?? 0);
        }

        try {
            $query->update([
                'is_read' => 1
            ]);
            return response()->json(['message' => '已读成功', 'code' => 200]);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => '已读失败', 'code' => 0]);

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 上传图片
     */
    public function upload(Request $request)
    {
        try {
            $upload = $request->file('file'); //
            $ext = $upload->extension();
            if (!in_array($ext, ['gif', 'jpeg', 'png', 'bmp'])) {
                return response()->json(['message' => '请选择正确的图片格式, gif jpeg png bmp', 'code' => 0]);
            }
            $path = $upload->store('image');
            return response()->json(['message' => '上传成功', 'code' => 200,
                'data' => ['image_path' => env('APP_URL', 'http://www.service.xitou.online') . '/storage/' . $path]]);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return response()->json(['message' => '上传失败', 'code' => 0]);
    }

    public function sts()
    {
        $data = [
            'AccessKeyId' => 'LTAI4FjywpLQbCe5eCtgWVVh',
            'AccessKeySecret' => 'zyl5d2GtZnicSJXDxLnxgPa7aUx6UI',
            'SecurityToken' => '',
        ];
        return response()->json(['message' => '已读失败', 'code' => 0, 'data' => $data]);

    }

    // 会话详情
    public function commDetail(Request $request)
    {
        $comm_id = $request->get('communication_id');
        $data = $this->message->where('communication_id', $comm_id)->get();
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $data]);

    }
}
