<?php

namespace App\Http\Middleware;

use App\Models\Utils;
use Closure;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;

class EnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $headers = getallheaders();

        /*         return Utils::success($headers);  */

        $user_id = 0;
        if ($headers != null) {
            if (isset($headers['User-Id'])) {
                $user_id = (($headers['User-Id']));
            }
        }
 
        if ($user_id < 1) {
            if ($request->user_id != null) {
                $user_id = ((int)($request->user_id));
            }
        }


        if ($user_id < 1) {
            return Utils::error($user_id . '<= Token not found.');
        }



        $u = Administrator::find($user_id);
        if ($u == null) {
            return Utils::error('User not found.');
        }
        $request->user = $u;
        return $next($request);
    }
}
