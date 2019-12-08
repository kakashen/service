<?php

namespace App\Http\Controllers;

use App\Model\Communication;
use App\Model\Message;
use App\Model\Staff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            if ($staff->active == 0) {
                return response()->json(['message' => '您的账号已被禁用, 请联系管理员', 'code' => 0]);
            }
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
        $nickname = $request->get('nickname');

        if (empty($username) || empty($password) || empty($nickname)) {
            return response()->json(['message' => '请输入账户名和密码', 'code' => 0]);
        }

        $staff = $this->staff;

        try {
            $staff_id = $staff->insertGetId([
                'username' => $username,
                'password' => sha1($this->salt . $request->input('password')),
                'nickname' => $nickname,
                'api_token' => uniqid(),
            ]);
            return response()->json(['message' => '注册成功', 'code' => 200, 'data' => ['id' => $staff_id]]);

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

        $info = Staff::select(['id', 'nickname as username', 'avatar'])
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
        $data = $this->staff->select('status', 'id')->find($staff_id);
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $data]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * 客服列表
     */
    public function staffList()
    {
        $data = $this->staff
            ->select('id', 'username', 'avatar', 'status', 'active', 'grade_avg')
            ->get();
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
        $days = ($end_time - $start_time) / 86400 + 1;

        // 保存每天日期
        $date = [];

        for ($i = 0; $i < $days; $i++) {
            $date[] = date('Y-m-d', $start_time + (86400 * $i));
        }

        $data = [];

        foreach ($date as $k => $item) {
            $list = [];
            $end = isset($date[$k + 1]) ? $date[$k + 1] : date('Y-m-d', strtotime($item) + (86400));
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
        $user = Auth::user();
        $user['roles'] = ['admin'];
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $user]);
    }

    // 上传客服头像
    public function uploadAvatar(Request $request)
    {
        try {
            $upload = $request->file('file');
            $ext = $upload->extension();
            if (!in_array($ext, ['gif', 'jpeg', 'png', 'bmp'])) {
                return response()->json(['message' => '请选择正确的图片格式, gif jpeg png bmp', 'code' => 0]);
            }
            $path = $upload->store('avatars');

            DB::table('avatars')->insert([
                'avatar' => $path
            ]);

            return response()->json(['message' => '上传成功', 'code' => 200,
                'data' => ['image_path' => env('APP_URL', 'http://www.service.xitou.online') . '/storage/' . $path]]);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return response()->json(['message' => '上传失败', 'code' => 0]);
    }

    // 头像列表
    public function avatarList()
    {
        $data = DB::table('avatars')->get();
        foreach ($data as &$datum) {
            $datum->avatar = env('APP_URL', 'http://www.service.xitou.online') . '/storage/' . $datum->avatar;
        }
        unset($datum);
        return response()->json(['message' => '获取成功', 'code' => 200, 'data' => $data]);

    }

    // 客服启用1 禁用0
    public function activeStaff(Request $request)
    {
        $active = $request->get('active');
        $id = $request->get('staff_id');
        if (!isset($active) || ($active != 0 && $active != 1)) {
            return response()->json(['message' => 'active 有误', 'code' => 0]);
        }

        if (!isset($id)) {
            return response()->json(['message' => 'staff_id 有误', 'code' => 0]);
        }

        try {
            $this->staff->where('id', $id)->update(['active' => $active]);
            return response()->json(['message' => '修改成功', 'code' => 200]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return response()->json(['message' => '修改失败', 'code' => 0]);
    }

    // 修改密码
    public function updatePass(Request $request)
    {
        $id = $request->get('staff_id');
        if (!isset($id)) {
            return response()->json(['message' => '客服id不能为空', 'code' => 0]);
        }

        $password = $request->get('staff_pass');
        if (!isset($password)) {
            return response()->json(['message' => '客服密码不能为空', 'code' => 0]);
        }

        $this->staff->where('id', $id)->update([
            'password' => sha1($this->salt . $password),
        ]);

        return response()->json(['message' => '修改成功', 'code' => 200]);
    }

    // 修改信息
    public function updateInfo(Request $request)
    {
        $avatar = $request->get('avatar');
        $nickname = $request->get('nickname');
        if (!isset($avatar, $nickname)) {
            return response()->json(['message' => '参数错误', 'code' => 0]);

        }
        $this->staff->where('id', Auth::user()->id)->update([
            'avatar' => $avatar,
            'nickname' => $nickname
        ]);
        return response()->json(['message' => '修改成功', 'code' => 200]);
    }

    // 客户评分
    public function grade(Request $request)
    {
        $client_id = $request->get('client_id');
        $staff_id = $request->get('staff_id');
        $grade = $request->get('grade');
        $communication_id = $request->get('communication_id');
        if (!isset($client_id, $staff_id, $grade, $communication_id)) {
            return response()->json(['message' => '参数错误', 'code' => 0]);
        }

        $ret = DB::table('staff_grades')->updateOrInsert([
            'client_id' => $client_id,
            'staff_id' => $staff_id,
            'communication_id' => $communication_id,
        ], [
            'grade' => $grade,
            // 'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($ret) {
            $query = Staff::where('id', $staff_id);
            $grade_num = $query->first()->grade_num;
            $grade_avg = $query->first()->grade_avg;

            $query->update([
                'grade_num' => $grade_num + 1,
                'grade_avg' => floor(($grade_avg * $grade_num + $grade) / ($grade_num + 1))
            ]);
        }
        return response()->json(['message' => '感谢您的评价', 'code' => 200]);

    }
}
