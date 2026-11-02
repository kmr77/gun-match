<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Sms\PhoneVerificationService;
use App\Services\Sms\SmsDeliveryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PhoneVerificationController extends Controller
{
    public function notice(Request $request): View|RedirectResponse
    {
        if (! $request->user()->phone) {
            return redirect()->route('mypage.profile');
        }

        if ($request->user()->phone_verified_at) {
            return redirect()->route('mypage');
        }

        return view('pages.phone-verification');
    }

    public function send(Request $request, PhoneVerificationService $service): RedirectResponse
    {
        try {
            $service->send($request->user());
        } catch (SmsDeliveryException) {
            return back()->withErrors(['phone' => 'SMSを送信できませんでした。送信事業者の設定後に再度お試しください。']);
        }

        return back()->with('status', '認証コードを送信しました。');
    }

    public function verify(Request $request, PhoneVerificationService $service): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'SMSで受信した6桁の認証コードを入力してください。',
            'code.digits' => '認証コードは6桁で入力してください。',
        ]);

        $service->verify($request->user(), $validated['code']);

        return redirect()->intended(route('mypage'))->with('status', '電話番号を確認しました。');
    }
}
