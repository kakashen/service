<?php

namespace App\Http\Controllers;

use App\Model\Communication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
    public function cGet(Request $request)
    {
        $client_id = $request->get('client_id');
        if (!$client_id) {
            return response()->json(['message' => '客户id不能为空', 'code' => 0]);
        }
        $data = $this->communication->where('client_id', $client_id)
            ->orderBy('updated_at', 'desc')->get();
        foreach ($data as &$datum) {
            $comm_id = $datum->id;
            $staff_id = $datum->staff_id;
            $grade = DB::table('staff_grades')->where('staff_id', $staff_id)
                ->where('client_id', $client_id)->where('communication_id', $comm_id)->first();
            $datum->isGrade = 0;
            $datum->grade = null;
            if ($grade) {
                $datum->isGrade = 1;
                $datum->grade = $grade->grade;
            }
        }
        unset($datum);
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

    public function communication(Request $request)
    {
        $client_id = $request->get('client_id');
        if (!isset($client_id)) {
            return response()->json(['message' => '客户id不能为空', 'code' => 0]);

        }

        $data = $this->communication->where('client_id', $client_id)->get();
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $data]);

    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 后台-会话列表
     */
    public function commList()
    {
        $data = $this->communication->orderBy('id', 'desc')->get();

        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $data]);

    }
}
