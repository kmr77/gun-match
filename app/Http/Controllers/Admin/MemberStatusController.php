<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MemberStatusController extends Controller
{
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['active', 'suspended'])],
        ]);

        $isActive = $validated['status'] === 'active';
        $actor = $request->user();

        if (! $isActive && $actor->is($user)) {
            return back()->withErrors(['status' => '現在ログイン中の管理者アカウントは停止できません。']);
        }

        $changed = DB::transaction(function () use ($request, $user, $actor, $isActive): bool {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $wasActive = $lockedUser->is_active;

            if ($wasActive === $isActive) {
                return true;
            }

            if (! $isActive && $lockedUser->is_admin) {
                $activeAdmins = User::query()
                    ->where('is_admin', true)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->get(['id']);

                if ($activeAdmins->count() <= 1) {
                    return false;
                }
            }

            $lockedUser->is_active = $isActive;
            if (! $isActive) {
                $lockedUser->remember_token = Str::random(60);
            }
            $lockedUser->save();

            if (! $isActive) {
                DB::table('sessions')->where('user_id', $lockedUser->id)->delete();
            }

            AdminActivityLog::create([
                'actor_user_id' => $actor->id,
                'target_user_id' => $lockedUser->id,
                'action' => $isActive ? 'member.activated' : 'member.suspended',
                'details' => ['from' => $wasActive ? 'active' : 'suspended', 'to' => $isActive ? 'active' : 'suspended'],
                'ip_address' => $request->ip(),
            ]);

            return true;
        });

        if (! $changed) {
            return back()->withErrors(['status' => '有効な管理者がいなくなるため、このアカウントは停止できません。']);
        }

        return back()->with('status', $isActive ? '会員アカウントを有効化しました。' : '会員アカウントを停止しました。');
    }
}
