<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    //管理者ログイン画面表示
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    //管理者ログイン処理
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ],[
            'email.required' => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',
        ]);

        if(Auth::guard('admin')->attempt(
            array_merge($credentials, ['role' => 1]), $request->boolean('remember')
        )) {
            $request->session()->regenerate();
            return redirect()->intended(route('admin.attendance.list'));
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
