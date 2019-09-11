<?php

namespace App\Http\Controllers;

use App\Model\Communication;
use Illuminate\Http\Request;

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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 客户获取会话id
     */
    public function cCommunication(Request $request)
    {
        $client_id = $request->get('client_id');
        if (!$client_id) {
            return response()->json(['message' => '客户id不能为空', 'code' => 0]);
        }
        $data = $this->communication->where('client_id', $client_id)
            ->orderBy('updated_at', 'desc')->get();
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $data]);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 客户关闭会话
     */
    public function cEnd(Request $request)
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
}
