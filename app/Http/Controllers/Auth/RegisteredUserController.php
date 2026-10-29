<?php

namespace App\Http\Controllers\Auth;

use App\Enums\MemberType;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => mb_strtolower(trim($request->input('email')))]);
        }

        $validated = $request->validate([
            'member_type' => ['required', Rule::enum(MemberType::class)],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ], [
            'member_type.required' => '会員区分を選択してください。',
            'member_type.enum' => '有効な会員区分を選択してください。',
            'name.required' => 'お名前を入力してください。',
            'name.max' => 'お名前は100文字以内で入力してください。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.unique' => 'このメールアドレスはすでに登録されています。',
            'password.required' => 'パスワードを入力してください。',
            'password.confirmed' => 'パスワードが一致しません。',
            'password.min' => 'パスワードは12文字以上で入力してください。',
        ]);

        $user = User::create([
            ...$validated,
            'member_type' => MemberType::from($validated['member_type']),
        ]);

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }
}
