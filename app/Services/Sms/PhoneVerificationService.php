<?php

namespace App\Services\Sms;

use App\Models\SmsVerificationChallenge;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class PhoneVerificationService
{
    public function __construct(private readonly SmsGateway $gateway) {}

    public function send(User $user): void
    {
        if ($user->phone_verified_at) {
            throw ValidationException::withMessages(['phone' => '電話番号はすでに確認済みです。']);
        }

        if (! $user->phone) {
            throw ValidationException::withMessages(['phone' => '先にプロフィールへ電話番号を登録してください。']);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $codeHash = Hash::make($code);
        $now = now();

        [$challenge, $phone] = DB::transaction(function () use ($user, $codeHash, $now): array {
            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();

            if (! $lockedUser->phone || $lockedUser->phone_verified_at) {
                throw ValidationException::withMessages(['phone' => '電話番号を登録し、未確認状態であることを確認してください。']);
            }

            $challenge = SmsVerificationChallenge::query()
                ->where('user_id', $lockedUser->id)
                ->lockForUpdate()
                ->first();

            if (! $challenge) {
                $challenge = new SmsVerificationChallenge(['user_id' => $lockedUser->id]);
            }

            if (! $challenge->window_started_at || $challenge->window_started_at->copy()->addHour()->isPast()) {
                $challenge->window_started_at = $now;
                $challenge->sends_in_window = 0;
            }

            if ($challenge->last_sent_at && $challenge->last_sent_at->copy()->addSeconds(60)->isFuture()) {
                throw ValidationException::withMessages(['phone' => '認証コードの再送信は60秒後にお試しください。']);
            }

            if ($challenge->sends_in_window >= 5) {
                throw ValidationException::withMessages(['phone' => '認証コードの送信上限に達しました。1時間後にお試しください。']);
            }

            $challenge->phone_hash = Hash::make($lockedUser->phone);
            $challenge->code_hash = $codeHash;
            $challenge->expires_at = $now->copy()->addMinutes(5);
            $challenge->last_sent_at = $now;
            $challenge->attempts = 0;
            $challenge->sends_in_window++;
            $challenge->save();

            return [$challenge, $lockedUser->phone];
        });

        try {
            $this->gateway->send($phone, "GunMatchの電話番号確認コードは{$code}です。有効期限は5分です。");
        } catch (SmsDeliveryException $exception) {
            $challenge->forceFill(['phone_hash' => null, 'code_hash' => null, 'expires_at' => null])->save();
            throw $exception;
        }
    }

    public function verify(User $user, string $code): void
    {
        if (! $user->phone) {
            throw ValidationException::withMessages(['phone' => '先にプロフィールへ電話番号を登録してください。']);
        }

        $error = DB::transaction(function () use ($user, $code): ?string {
            $challenge = SmsVerificationChallenge::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (
                ! $challenge
                || ! $challenge->phone_hash
                || ! $user->phone
                || ! Hash::check($user->phone, $challenge->phone_hash)
                || ! $challenge->code_hash
                || ! $challenge->expires_at
            ) {
                return '有効な認証コードを先に送信してください。';
            }

            if ($challenge->expires_at->isPast()) {
                $challenge->forceFill(['phone_hash' => null, 'code_hash' => null, 'expires_at' => null])->save();

                return '認証コードの有効期限が切れました。再送信してください。';
            }

            if ($challenge->attempts >= 5) {
                $challenge->forceFill(['phone_hash' => null, 'code_hash' => null, 'expires_at' => null])->save();

                return '入力試行回数の上限に達しました。新しいコードを送信してください。';
            }

            if (! Hash::check($code, $challenge->code_hash)) {
                $challenge->attempts++;
                if ($challenge->attempts >= 5) {
                    $challenge->phone_hash = null;
                    $challenge->code_hash = null;
                    $challenge->expires_at = null;
                }
                $challenge->save();

                return '認証コードが正しくありません。';
            }

            $user->forceFill(['phone_verified_at' => now()])->save();
            $challenge->forceFill(['phone_hash' => null, 'code_hash' => null, 'expires_at' => null])->save();

            return null;
        });

        if ($error !== null) {
            throw ValidationException::withMessages(['code' => $error]);
        }
    }
}
