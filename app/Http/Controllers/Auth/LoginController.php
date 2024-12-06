<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function sendLoginLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();
        $user->login_token = Str::random(60);
        $user->save();

        Mail::to($user->email)->send(new \App\Mail\LoginLink($user));

        return back()->with('message', 'Login link sent to your email!');
    }

    public function processLoginLink(Request $request, $token)
    {
        $user = User::where('login_token', $token)->firstOrFail();

        $rememberLogin = true;
        Auth::login($user, $rememberLogin);

        $user->login_token = null;
        $user->save();

        return redirect()->intended('/');
    }
}
