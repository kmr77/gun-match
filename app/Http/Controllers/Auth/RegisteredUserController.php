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

        $eligibleBirthDate = now()->subYears(18)->toDateString();

        $validated = $request->validate([
            'member_type' => ['required', Rule::enum(MemberType::class)],
            'legal_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'prefecture' => ['required', Rule::in(array_keys(config('gunmatch_registration.prefectures')))],
            'birth_date' => ['required', 'date', 'before_or_equal:'.$eligibleBirthDate],
            'phone' => ['nullable', 'string', 'max:30'],
            'has_firearm_permit' => ['nullable', 'boolean'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ], [
            'member_type.required' => '会員区分を選択してください。',
            'member_type.enum' => '有効な会員区分を選択してください。',
            'legal_name.required' => '本名を入力してください。',
            'legal_name.max' => '本名は255文字以内で入力してください。',
            'name.required' => '表示名を入力してください。',
            'name.max' => '表示名は100文字以内で入力してください。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => '有効なメールアドレスを入力してください。',
            'email.unique' => 'このメールアドレスはすでに登録されています。',
            'prefecture.required' => '都道府県を選択してください。',
            'prefecture.in' => '有効な都道府県を選択してください。',
            'birth_date.required' => '生年月日を入力してください。',
            'birth_date.date' => '有効な生年月日を入力してください。',
            'birth_date.before_or_equal' => '18歳未満の方は会員登録できません。',
            'has_firearm_permit.boolean' => '所持許可の自己申告を選択してください。',
            'password.required' => 'パスワードを入力してください。',
            'password.confirmed' => 'パスワードが一致しません。',
            'password.min' => 'パスワードは12文字以上で入力してください。',
        ]);

        $user = User::create([
            ...$validated,
            'member_type' => MemberType::from($validated['member_type']),
            'has_firearm_permit' => $validated['has_firearm_permit'] ?? null,
        ]);

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }
}
