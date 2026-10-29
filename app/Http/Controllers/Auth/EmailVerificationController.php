<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationController extends Controller
{
    public function notice(): View
    {
        return view('pages.email-verification');
    }

    public function verify(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('mypage');
        }

        $request->fulfill();

        return redirect()->route('mypage')->with('status', 'メールアドレスを確認しました。');
    }

    public function send(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('status', 'メールアドレスはすでに確認済みです。');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', '確認メールを送信しました。');
    }
}
