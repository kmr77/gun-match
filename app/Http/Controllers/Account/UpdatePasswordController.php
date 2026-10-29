<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ], [
            'current_password.required' => '現在のパスワードを入力してください。',
            'current_password.current_password' => '現在のパスワードが正しくありません。',
            'password.required' => '新しいパスワードを入力してください。',
            'password.confirmed' => 'パスワードが一致しません。',
            'password.min' => 'パスワードは12文字以上で入力してください。',
        ]);

        $request->user()->update(['password' => $validated['password']]);

        return back()->with('status', 'パスワードを更新しました。');
    }
}
