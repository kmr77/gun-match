<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        if (is_string($request->input('email'))) {
            $request->merge(['email' => mb_strtolower(trim($request->input('email')))]);
        }

        $user = $request->user();
        $eligibleBirthDate = now()->subYears(18)->toDateString();
        $validated = $request->validate([
            'legal_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'prefecture' => ['required', Rule::in(array_keys(config('gunmatch_registration.prefectures')))],
            'birth_date' => ['required', 'date', 'before_or_equal:'.$eligibleBirthDate],
            'phone' => ['required', 'string', 'max:30'],
            'has_firearm_permit' => ['nullable', 'boolean'],
        ], [
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
            'birth_date.before_or_equal' => '18歳未満の方は登録情報を保存できません。',
            'phone.required' => '電話番号を入力してください。',
            'phone.max' => '電話番号は30文字以内で入力してください。',
            'has_firearm_permit.boolean' => '所持許可の自己申告を選択してください。',
        ]);

        $emailChanged = $validated['email'] !== $user->email;
        $phoneChanged = ($validated['phone'] ?? null) !== $user->phone;
        $user->fill([
            ...$validated,
            'phone' => $validated['phone'] ?? null,
            'has_firearm_permit' => $validated['has_firearm_permit'] ?? null,
        ]);
        $changedFields = array_keys($user->getDirty());
        if ($emailChanged) {
            $changedFields[] = 'email_verified_at';
        }
        if ($phoneChanged && $user->phone_verified_at !== null) {
            $changedFields[] = 'phone_verified_at';
        }

        DB::transaction(function () use ($user, $emailChanged, $phoneChanged, $changedFields): void {
            if ($emailChanged) {
                $user->email_verified_at = null;
            }
            if ($phoneChanged) {
                $user->phone_verified_at = null;
                DB::table('sms_verification_challenges')
                    ->where('user_id', $user->id)
                    ->update([
                        'phone_hash' => null,
                        'code_hash' => null,
                        'expires_at' => null,
                        'attempts' => 0,
                        'updated_at' => now(),
                    ]);
            }
            $user->save();

            if ($changedFields !== []) {
                AdminActivityLog::create([
                    'actor_user_id' => $user->id,
                    'target_user_id' => $user->id,
                    'action' => 'member.profile_updated',
                    'details' => ['changed_fields' => $changedFields],
                ]);
            }
        });

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return back()->with('status', $changedFields === [] ? '変更内容はありません。' : 'プロフィールを更新しました。');
    }
}
