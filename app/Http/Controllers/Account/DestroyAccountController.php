<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DestroyAccountController extends Controller
{
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ], [
            'password.required' => '現在のパスワードを入力してください。',
            'password.current_password' => '現在のパスワードが正しくありません。',
        ]);

        $user = $request->user();
        $userId = $user->getKey();
        $email = $user->email;

        Auth::guard('web')->logout();

        DB::transaction(function () use ($user, $userId, $email): void {
            DB::table('sessions')->where('user_id', $userId)->delete();
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            $user->delete();
        });

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'アカウントを削除しました。');
    }
}
