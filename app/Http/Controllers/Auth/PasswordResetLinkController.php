<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class PasswordResetLinkController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => mb_strtolower(trim($request->input('email')))]);
        }

        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
        ]);

        try {
            Password::sendResetLink($request->only('email'));
        } catch (TransportExceptionInterface) {
            Log::warning('Authentication email delivery failed.', ['flow' => 'password_reset']);
            $user = User::query()->where('email', $request->input('email'))->first();
            if ($user !== null) {
                Password::deleteToken($user);
            }

            return back()->with('mail_delivery_error', 'パスワード再設定メールを送信できませんでした。時間をおいて再度お試しください。');
        }

        return back()->with(
            'status',
            '登録済みのメールアドレスの場合、パスワード再設定の案内を送信しました。',
        );
    }
}
