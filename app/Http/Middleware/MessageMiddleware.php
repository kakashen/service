<?php

namespace App\Http\Middleware;

use App\Model\Staff;
use Closure;

class MessageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $to = $request->get('to'); // 接收人id
        $direction = $request->get('direction'); // 1客户发给客服, 2客服发给客户
        if ($direction == 1) {
            $staff = new Staff();
            $info = $staff->find($to);


        }
        return $next($request);
    }
}
