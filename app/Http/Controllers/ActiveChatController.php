<?php

namespace App\Http\Controllers;

use App\Model\ActiveChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActiveChatController extends Controller
{
    private $chat;

    /**
     * Create a new controller instance.
     *
     * @param ActiveChat $chat
     */
    public function __construct(ActiveChat $chat)
    {
        $this->chat = $chat;
    }

    public function get()
    {
        $staff_id = Auth::user()->id;
        $data = $this->chat->where('staff_id', $staff_id)->get();
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $data]);

    }

    public function delete(Request $request)
    {
        $chat__id = $request->input('chat_id');
        if (!isset($chat__id)) {
            return response()->json(['message' => 'chat_id不能为空', 'code' => 0]);
        }

        $staff_id = Auth::user()->id;

        $ret = $this->chat->where('staff_id', $staff_id)
            ->delete($chat__id);

        if ($ret) {
            return response()->json(['message' => '删除成功', 'code' => 200]);
        }
        return response()->json(['message' => '删除失败', 'code' => 0]);

    }

}
