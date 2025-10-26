<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Actions\Fortify\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ログイン時のリダイレクトを上書き
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);

        // ログアウト時のリダイレクトを上書き
        $this->app->singleton(LogoutResponseContract::class, function ($app) {
            return new class implements LogoutResponseContract {
                public function toResponse($request)
                {
                    // 管理者ログアウト時
                    if ($request->is('admin/*') || Auth::guard('admin')->check()) {
                        Auth::guard('admin')->logout();
                        $request->session()->invalidate();
                        $request->session()->regenerateToken();
                        return redirect('/admin/login');
                    }

                    // 一般ユーザー
                    Auth::guard('web')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect('/login');
                }
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('user.auth.register');
        });

        Fortify::loginView(function () {
            if (request()->is('admin/*')) {
                return view('admin.auth.login');
            }
            return view('user.auth.login');
        });

        // ログイン認証ロジック
        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return null;
            }

            if (request()->is('admin/*') && $user->role === 1) {
                config(['fortify.guard' => 'admin']);
                config(['auth.defaults.guard' => 'admin']);
                Auth::setDefaultDriver('admin');
                Auth::shouldUse('admin');
                return $user;
            }

            if (!request()->is('admin/*') && $user->role === 0) {
                config(['fortify.guard' => 'web']);
                config(['auth.defaults.guard' => 'web']);
                Auth::setDefaultDriver('web');
                Auth::shouldUse('web');
                return $user;
            }

            return null;
        });

        // ログイン成功時のリダイレクト
        Fortify::redirects('login', function () {
            if (Auth::guard('admin')->check()) {
                return route('admin.attendance.list');
            }
            return route('attendance.index');
        });

        // ログイン試行制限
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });

    }

}
