<?php

namespace App\Http\Controllers;

use App\Model\CustomerServiceMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerServiceMessageController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function add(Request $request)
    {
        $message = $request->get('message');
        $user = Auth::user();
        $service_message = new CustomerServiceMessage();
        $service_message->staff_id = $user->id;
        $service_message->message = $message;
        $ret = $service_message->save();
        if ($ret) {
            return response()->json(['message' => '保存成功', 'code' => 200]);
        }
        return response()->json(['message' => '保存失败', 'code' => 0]);

    }

    public function get()
    {
        $user = Auth::user();
        $service_message = new CustomerServiceMessage();
        $data = $service_message->where(['staff_id' => $user->id])->get();

        if (count($data)) {
            return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $data]);
        }
        return response()->json(['message' => '获取失败', 'code' => 0]);

    }

    public function update(Request $request)
    {
        $message = $request->input('message');
        if (empty($message)) {
            return response()->json(['message' => '消息不能为空', 'code' => 0]);
        }

        $message_id = $request->input('id');
        if (empty($message_id)) {
            return response()->json(['message' => '消息id不能为空', 'code' => 0]);
        }

        $user = Auth::user();

        $ret = CustomerServiceMessage::where('id', $message_id)
            ->where('staff_id', $user->id)
            ->update(['message' => $message]);

        if ($ret) {
            return response()->json(['message' => '修改成功', 'code' => 200]);
        }
        return response()->json(['message' => '修改失败', 'code' => 0]);

    }

}
