<?php

namespace App\Console\Commands;

use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ManageAdministrator extends Command
{
    protected $signature = 'gunmatch:admin {email : 対象会員のメールアドレス} {--revoke : 管理者権限を解除する}';

    protected $description = '会員に管理者権限を付与または解除します（CLI専用）';

    public function handle(): int
    {
        $email = mb_strtolower(trim((string) $this->argument('email')));
        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->error('指定された会員が見つかりません。');

            return self::FAILURE;
        }

        $grant = ! $this->option('revoke');

        if ($user->is_admin === $grant) {
            $this->info('権限はすでに指定した状態です。');

            return self::SUCCESS;
        }

        $verb = $grant ? '付与' : '解除';
        if (! $this->confirm("{$user->email} の管理者権限を{$verb}しますか？")) {
            $this->warn('変更を中止しました。');

            return self::FAILURE;
        }

        $changed = DB::transaction(function () use ($user, $grant): bool {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($grant && (! $lockedUser->is_active || ! $lockedUser->hasVerifiedEmail())) {
                return false;
            }

            if (! $grant && $lockedUser->is_admin) {
                $activeAdmins = User::query()
                    ->where('is_admin', true)
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->get(['id']);

                if ($lockedUser->is_active && $activeAdmins->count() <= 1) {
                    return false;
                }
            }

            $lockedUser->is_admin = $grant;
            $lockedUser->save();

            AdminActivityLog::create([
                'actor_label' => 'CLI operator',
                'target_user_id' => $lockedUser->id,
                'action' => $grant ? 'admin.granted' : 'admin.revoked',
                'details' => ['email' => $lockedUser->email],
            ]);

            return true;
        });

        if (! $changed) {
            $this->error($grant
                ? '停止中またはメール未確認の会員には管理者権限を設定できません。'
                : '最後の有効な管理者権限は解除できません。');

            return self::FAILURE;
        }

        $this->info("{$user->email} の管理者権限を{$verb}しました。");

        return self::SUCCESS;
    }
}
