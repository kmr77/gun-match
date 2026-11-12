<?php

namespace App\Http\Controllers\Auth;

use App\Enums\MemberType;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\LegalTerms;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class RegisteredUserController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => mb_strtolower(trim($request->input('email')))]);
        }

        $eligibleBirthDate = now()->subYears(18)->toDateString();

        $termsVersion = LegalTerms::registrationVersion();
        $termsVersionRules = $termsVersion === null
            ? ['prohibited']
            : ['required', 'string', Rule::in([$termsVersion])];

        $validated = $request->validate([
            'member_type' => ['required', Rule::enum(MemberType::class)],
            'legal_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'prefecture' => ['required', Rule::in(array_keys(config('gunmatch_registration.prefectures')))],
            'birth_date' => ['required', 'date', 'before_or_equal:'.$eligibleBirthDate],
            'phone' => ['required', 'string', 'max:30'],
            'has_firearm_permit' => ['nullable', 'boolean'],
            'terms_version' => $termsVersionRules,
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
            'phone.required' => '電話番号を入力してください。',
            'phone.max' => '電話番号は30文字以内で入力してください。',
            'has_firearm_permit.boolean' => '所持許可の自己申告を選択してください。',
            'terms_version.required' => '利用規約への同意が必要です。',
            'terms_version.in' => '現在有効な利用規約への同意を確認できません。',
            'terms_version.prohibited' => '現在、正式な利用規約への同意登録は受け付けていません。',
            'password.required' => 'パスワードを入力してください。',
            'password.confirmed' => 'パスワードが一致しません。',
            'password.min' => 'パスワードは12文字以上で入力してください。',
        ]);

        $user = DB::transaction(function () use ($validated, $termsVersion): User {
            $termsVersion = $validated['terms_version'] ?? null;
            unset($validated['terms_version']);

            $user = User::create([
                ...$validated,
                'member_type' => MemberType::from($validated['member_type']),
                'has_firearm_permit' => $validated['has_firearm_permit'] ?? null,
            ]);

            if ($termsVersion !== null) {
                $user->termsConsents()->create([
                    'terms_version' => $termsVersion,
                    'consented_at' => now(),
                    'consent_method' => 'registration_checkbox',
                ]);
            }

            return $user;
        });

        $mailDeliveryFailed = false;
        try {
            event(new Registered($user));
        } catch (TransportExceptionInterface) {
            $mailDeliveryFailed = true;
            Log::warning('Authentication email delivery failed.', ['flow' => 'registration_verification']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        $response = redirect()->route('verification.notice');

        return $mailDeliveryFailed
            ? $response->with('mail_delivery_error', '確認メールを送信できませんでした。時間をおいて再送してください。')
            : $response;
    }
}
