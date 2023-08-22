<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class OAuth2Controller extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function index(Request $request)
    {
        return Socialite::driver('asan')->redirect();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function success(Request $request)
    {
        $asan = Socialite::driver('asan')->user();

        \Session::put('asanUser', [
            'expiresIn' => $asan->expiresIn,
            'accessToken' => $asan->token,
            'scopes' => $asan->approvedScopes,
            'certificates' => $asan->user['certificates'],
            'loginDetail' => $asan->user['loginDetail']
        ]);


        //$asan->user['data'] data provided by the scope asan login

        $user = User::where('data_field', $asan->user['data'])->first();

        if (!empty($user)) {
            \Auth::guard('web')->login($user);
            return redirect()->route('home');
        }
    }

}
