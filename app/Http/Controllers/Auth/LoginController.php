<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Override login to check if user is active (status = 1)
     */
    public function login(Request $request)
    {
        $this->validateLogin($request);

        // Check if user exists and is not disabled (status = 2) before attempting login
        $user = \App\User::where('email', $request->email)->first();
        if ($user && $user->status == 2) {
            return $this->sendFailedLoginResponse($request, 'Votre compte a été désactivé. Contactez votre administrateur.');
        }

        if (method_exists($this, 'hasTooManyLoginAttempts') &&
            $this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

    /**
     * Override sendFailedLoginResponse to support custom message
     */
    protected function sendFailedLoginResponse(Request $request, $message = null)
    {
        if ($message) {
            return redirect()->back()
                ->withInput($request->only($this->username(), 'remember'))
                ->withErrors([$this->username() => $message]);
        }

        return redirect()->back()
            ->withInput($request->only($this->username(), 'remember'))
            ->withErrors([$this->username() => trans('auth.failed')]);
    }
}
