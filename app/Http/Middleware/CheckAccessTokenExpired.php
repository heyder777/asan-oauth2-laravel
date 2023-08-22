<?php

namespace App\Http\Middleware;

use App\Helpers\Helper;
use Auth;
use Closure;
use function redirect;

class CheckAccessTokenExpired
{

    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (\Session::has('asanUser')) {
            $accessToken = Helper::getAccessTokenPayload(\Session::get('asanUser')['accessToken']);
            if (!empty($accessToken) && $accessToken['exp'] < time()) {
                Auth::guard()->logout();
                $request->session()->flush();
                $request->session()->regenerate();
                return redirect()->route('oauth2.login');
            }
        }

        return $next($request);
    }
}
