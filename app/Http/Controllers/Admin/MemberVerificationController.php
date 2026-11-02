<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MemberType;
use App\Enums\MemberVerificationStatus;
use App\Enums\MemberVerificationType;
use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MemberVerificationController extends Controller
{
    public function update(Request $request, User $user): RedirectResponse
    {
        $allowedTypes = $user->member_type === MemberType::Individual
            ? [MemberVerificationType::Identity->value]
            : [MemberVerificationType::Business->value];

        $validated = $request->validate([
            'verification_status' => ['required', Rule::enum(MemberVerificationStatus::class)],
            'verification_type' => ['required', Rule::in($allowedTypes)],
            'verification_method' => [
                Rule::requiredIf($request->input('verification_status') === MemberVerificationStatus::Verified->value),
                'nullable',
                'string',
                'max:255',
            ],
            'verified_at' => [
                Rule::requiredIf($request->input('verification_status') === MemberVerificationStatus::Verified->value),
                'nullable',
                'date_format:Y-m-d',
                'before_or_equal:today',
            ],
            'verification_note' => ['nullable', 'string', 'max:10000'],
        ], [
            'verification_status.required' => '確認状態を選択してください。',
            'verification_status.enum' => '有効な確認状態を選択してください。',
            'verification_type.required' => '確認区分を選択してください。',
            'verification_type.in' => '会員区分に合った確認区分を選択してください。',
            'verification_method.required' => '確認済みにする場合は、実際に行った確認方法を入力してください。',
            'verification_method.max' => '確認方法は255文字以内で入力してください。',
            'verified_at.required' => '確認済みにする場合は、実際に確認した日を入力してください。',
            'verified_at.date_format' => '確認日は正しい形式で入力してください。',
            'verified_at.before_or_equal' => '確認日に未来の日付は指定できません。',
            'verification_note.max' => '審査メモは10,000文字以内で入力してください。',
        ]);

        DB::transaction(function () use ($request, $user, $validated): void {
            $reviewedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $previousStatus = $reviewedUser->verification_status;
            $previousType = $reviewedUser->verification_type;
            $noteChanged = $reviewedUser->verification_note !== ($validated['verification_note'] ?? null);
            $admin = $request->user();

            $reviewedUser->verification_status = $validated['verification_status'];
            $reviewedUser->verification_type = $validated['verification_type'];
            $reviewedUser->verification_note = $validated['verification_note'] ?? null;
            $reviewedUser->reviewed_by_user_id = $admin->id;
            $reviewedUser->reviewed_at = now();

            if ($validated['verification_status'] === MemberVerificationStatus::Verified->value) {
                $reviewedUser->verification_method = $validated['verification_method'];
                $reviewedUser->verified_at = Carbon::createFromFormat('Y-m-d', $validated['verified_at'])->startOfDay();
            }

            $reviewedUser->save();

            AdminActivityLog::create([
                'actor_user_id' => $admin->id,
                'target_user_id' => $reviewedUser->id,
                'action' => 'member.verification_updated',
                'details' => [
                    'from_status' => $previousStatus->value,
                    'to_status' => $reviewedUser->verification_status->value,
                    'from_type' => $previousType?->value,
                    'to_type' => $reviewedUser->verification_type->value,
                    'verification_method_recorded' => $reviewedUser->verification_status === MemberVerificationStatus::Verified,
                    'verification_date_recorded' => $reviewedUser->verification_status === MemberVerificationStatus::Verified,
                    'note_changed' => $noteChanged,
                ],
                'ip_address' => $request->ip(),
            ]);
        });

        return redirect()->route('admin.members.show', $user)
            ->with('status', '会員確認状況を更新しました。');
    }
}
