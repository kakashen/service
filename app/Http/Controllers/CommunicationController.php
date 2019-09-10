<?php

namespace App\Http\Controllers;

use App\Model\Communication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunicationController extends Controller
{
    private $communication;

    public function __construct(Communication $communication)
    {
        $this->communication = $communication;
    }

    public function end(Request $request)
    {
        $communication_id = $request->get('communication_id');
        if (!isset($communication_id)) {
            return response()->json(['message' => '会话不能为空', 'code' => 0]);
        }

        $ret = $this->communication->where('id', $communication_id)
            ->update(['status' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        if ($ret) {
            return response()->json(['message' => '关闭成功', 'code' => 200]);
        }
        return response()->json(['message' => '关闭失败', 'code' => 0]);

    }

    public function getCommunication(Request $request)
    {
        $type = $request->get('type', 1); // 1=客服
        $params = [];

        if ($type == 1) {
            $params['client_id'] = Auth::user()->id;
        }
        if ($type != 1) {
            $params['staff_id'] = Auth::user()->id;
        }
        $data = $this->communication->where($params)->get();
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $data]);
    }
}
