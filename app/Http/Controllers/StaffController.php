<?php

namespace App\Http\Controllers;

use App\Model\Communication;
use App\Model\Message;
use App\Model\Staff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    private $staff;
    private $salt;

    public function __construct(Staff $staff)
    {
        $this->staff = $staff;
        $this->salt = "userLoginRegister";
    }

    //登录
    public function login(Request $request)
    {
        $username = $request->get('username');
        if (empty($username)) {
            return response()->json(['message' => '请输入账号', 'code' => 0]);
        }
        $password = $request->get('password');
        if (empty($username)) {
            return response()->json(['message' => '请输入密码', 'code' => 0]);
        }

        $staff = $this->staff::where('username', $username)->where('password', sha1($this->salt . $password))->first();
        if ($staff) {
            $staff->api_token = uniqid();
            $staff->status = $request->get('status') ?? 1; // 登录状态
            $staff->save();
            return response()->json(['message' => '登录成功', 'code' => 200,
                'data' => $staff]);
        } else {
            return response()->json(['message' => '账号或密码错误', 'code' => 0]);
        }

    }

    //注册
    public function register(Request $request)
    {
        $username = $request->get('username');
        $password = $request->get('password');
        if (empty($username) || empty($password)) {
            return response()->json(['message' => '请输入账户名和密码', 'code' => 0]);
        }

        $staff = $this->staff;
        $staff->username = $request->input('username');
        $staff->password = sha1($this->salt . $request->input('password'));
        $staff->api_token = uniqid();

        try {
            $staff->save();
            return response()->json(['message' => '注册成功', 'code' => 200]);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => '用户名已存在', 'code' => 0]);
        }
    }

    public function updateStatus(Request $request)
    {
        $status = $request->get('status', 1);
        if ($status == 0) {
            // 有未结束会话不能为非上线状态
            $staff_id = Auth::user()->id;
            $count = Communication::where('staff_id', $staff_id)
                ->where('status', 1)->count();
            if ($count) {
                return response()->json(['message' => '请先结束会话', 'code' => 0]);
            }
        }
        // 过期的 token 不应该还请求后端 delete（也就不存在服务端报错），直接前端清除缓存退出即可
        $ret = $this->staff::where('id', Auth::user()->id)->update(['status' => $status]);
        if ($ret) {
            return response()->json(['message' => '修改状态成功', 'code' => 200]);
        }
        return response()->json(['message' => '修改状态失败', 'code' => 0]);

    }


    //客服信息
    public function info(Request $request)
    {
        $staff_id = $request->get('staff_id');
        if (!isset($staff_id)) {
            return response()->json(['message' => '客服id不能为空', 'code' => 0]);
        }

        $info = Staff::select(['id', 'username', 'avatar'])
            ->where('id', $staff_id)->first();
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $info]);


    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 获取客服状态
     */
    public function getStatus()
    {
        $staff_id = Auth::user()->id;
        $data = $this->staff->select('status','id')->find($staff_id);
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $data]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 客服列表
     */
    public function staffList()
    {
        $data = $this->staff->select('id', 'username', 'avatar', 'status')->get();
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $data]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 后台-首页数据
     */
    public function index(Request $request)
    {
        $start_time = $request->get('start_time');
        $end_time = $request->get('end_time');
        // 计算日期段内有多少天
        $days = ($end_time-$start_time)/86400+1;

        // 保存每天日期
        $date = [];

        for($i=0; $i<$days; $i++){
            $date[] = date('Y-m-d', $start_time+(86400*$i));
        }

        $data = [];

        foreach ($date as $k => $item) {
            $list = [];
            $end = isset($date[$k+1]) ? $date[$k+1] : date('Y-m-d', strtotime($item)+(86400));
            // 客户消息数
            $client_message_count = Message::where('direction', 1)->whereBetween('created_at', [$item, $end])->count();
            // 客服消息数
            $staff_message_count = Message::where('direction', 2)->whereBetween('created_at', [$item, $end])->count();
            // 会话数
            $comm_count = Communication::whereBetween('created_at', [$item, $end])->count();

            $list['client_message_count'] = $client_message_count;
            $list['staff_message_count'] = $staff_message_count;
            $list['comm_count'] = $comm_count;
            $list['date'] = $item;

            $data[] = $list;
        }
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $data]);

    }

    public function admin()
    {
        $user =  Auth::user();
        $user['roles'] = ['admin'];
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $user]);
    }
}
