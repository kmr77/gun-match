<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class NewPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('pages.auth-preview', [
            'page' => [
                'title' => 'パスワード再設定',
                'description' => '新しいパスワードを設定してください。',
                'notice' => '再設定リンクは有効期限内にご利用ください。',
                'button' => 'パスワードを更新',
                'fields' => [
                    ['name' => 'email', 'label' => 'メールアドレス', 'type' => 'email', 'autocomplete' => 'email'],
                    ['name' => 'password', 'label' => '新しいパスワード', 'type' => 'password', 'autocomplete' => 'new-password'],
                    ['name' => 'password_confirmation', 'label' => '新しいパスワード（確認）', 'type' => 'password', 'autocomplete' => 'new-password'],
                ],
                'links' => [
                    ['label' => 'ログインへ戻る', 'route' => 'login'],
                ],
            ],
            'pageKey' => 'reset-password',
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => mb_strtolower(trim($request->input('email')))]);
        }

        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(12)],
        ], [
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'password.required' => '新しいパスワードを入力してください。',
            'password.confirmed' => 'パスワードが一致しません。',
            'password.min' => 'パスワードは12文字以上で入力してください。',
        ]);

        $status = Password::reset(
            $validated,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'パスワードを更新しました。');
        }

        return back()
            ->withErrors(['email' => '再設定リンクが無効または期限切れです。もう一度再設定を申請してください。'])
            ->onlyInput('email');
    }
}
