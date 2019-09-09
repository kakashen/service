<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    private $user;
    private $salt;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->salt = "userLoginRegister";
    }

    //登录
    public function login(Request $request)
    {
        if ($request->has('username') && $request->has('password')) {
            $user = User::where('username', '=', $request->input('username'))->where('password', '=', sha1($this->salt . $request->input('password')))->first();
            if ($user) {
                $user->api_token = uniqid();
                $user->save();
                return response()->json(['message' => '登录成功', 'code' => 200,
                    'api_token' => $user->api_token]);
            } else {
                return response()->json(['message' => '账号或密码错误', 'code' => 0]);
            }
        } else {
            return response()->json(['message' => '未知错误', 'code' => 0]);
        }
    }

    //注册
    public function register(Request $request)
    {
        if ($request->has('username') && $request->has('password')) {
            $user = new User;
            $user->username = $request->input('username');
            $user->password = sha1($this->salt . $request->input('password'));
            $user->api_token = Str::random(32);;
            if ($user->save()) {
                return response()->json(['message' => '注册成功', 'code' => 200]);
            } else {
                return response()->json(['message' => '注册失败, 请稍后再试', 'code' => 0]);

            }
        } else {
            return response()->json(['message' => '请输入完整用户信息', 'code' => 0]);
        }
    }

    public function logout()
    {
        // 过期的 token 不应该还请求后端 delete（也就不存在服务端报错），直接前端清除缓存退出即可
        return response()->json(['message' => '退出成功', 'code' => 200]);
    }


    //信息
    public function info()
    {
        return Auth::user();
    }
}
