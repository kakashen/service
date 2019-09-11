<?php

namespace App\Http\Controllers;

use App\Model\Staff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\This;

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
        if ($request->has('username') && $request->has('password')) {
            $staff = $this->staff;
            $staff->username = $request->input('username');
            $staff->password = sha1($this->salt . $request->input('password'));
            $staff->api_token = Str::random(32);;
            if ($staff->save()) {
                return response()->json(['message' => '注册成功', 'code' => 200]);
            } else {
                return response()->json(['message' => '注册失败, 请稍后再试', 'code' => 0]);

            }
        } else {
            return response()->json(['message' => '请输入完整用户信息', 'code' => 0]);
        }
    }

    public function updateStatus(Request $request)
    {
        $status = $request->get('status', 1);
        // 过期的 token 不应该还请求后端 delete（也就不存在服务端报错），直接前端清除缓存退出即可
        $ret = $this->staff::where('id', Auth::user()->id)->update(['status' => $status]);
        if ($ret) {
            return response()->json(['message' => '修改状态成功', 'code' => 200]);
        }
        return response()->json(['message' => '修改状态失败', 'code' => 0]);

    }


    //信息
    public function info()
    {
        return Auth::user();
    }
}
