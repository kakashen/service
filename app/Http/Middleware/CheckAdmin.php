<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckAdmin
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
        $uri = $request->path();
        if ($uri == 'api/admin/admin') {
            return $next($request);
        }
        if (Auth::user()->username != 'admin') {
            return response()->json(['message' => '权限不够, 请联系管理员.', 'code' => 401]);
        }

        return $next($request);
    }
}
