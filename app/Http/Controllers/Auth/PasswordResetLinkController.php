<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

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

        Password::sendResetLink($request->only('email'));

        return back()->with(
            'status',
            '登録済みのメールアドレスの場合、パスワード再設定の案内を送信しました。',
        );
    }
}
