@extends('layouts.app')

@section('title', '会員詳細｜GunMatch')

@section('content')
    <section class="page-section">
        <div class="container">
            <x-page-heading
                title="会員詳細"
                description="会員情報と、この会員を対象とした管理操作履歴を確認できます。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => '管理者ページ', 'route' => 'admin.dashboard'], ['label' => '会員詳細']]"
            />
            @if (session('status'))
                <x-feedback-message type="success" title="更新しました" :message="session('status')" />
            @endif
            @if ($errors->any())
                <x-feedback-message type="error" title="処理を完了できません" :message="$errors->first()" />
            @endif

            <section class="admin-panel" aria-labelledby="admin-member-details-title">
                <h2 id="admin-member-details-title">会員情報</h2>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <tbody>
                            <tr><th scope="row">会員ID</th><td>{{ $user->id }}</td></tr>
                            <tr><th scope="row">氏名</th><td>{{ $user->name }}</td></tr>
                            <tr><th scope="row">メールアドレス</th><td>{{ $user->email }}</td></tr>
                            <tr><th scope="row">会員区分</th><td>{{ $user->member_type->label() }}</td></tr>
                            <tr>
                                <th scope="row">登録日時</th>
                                <td><time datetime="{{ $user->created_at->toIso8601String() }}">{{ $user->created_at->format('Y-m-d H:i:s') }}</time></td>
                            </tr>
                            <tr><th scope="row">メール認証状態</th><td>{{ $user->hasVerifiedEmail() ? '認証済み' : '未認証' }}</td></tr>
                            <tr><th scope="row">利用状態</th><td>{{ $user->is_active ? '有効' : '停止中' }}</td></tr>
                            <tr><th scope="row">管理者権限</th><td>{{ $user->is_admin ? 'あり' : 'なし' }}</td></tr>
                            <tr><th scope="row">会員確認状態</th><td>{{ $user->verification_status->label() }}</td></tr>
                            <tr><th scope="row">確認区分</th><td>{{ $user->verification_type?->label() ?? '未設定' }}</td></tr>
                            <tr>
                                <th scope="row">審査担当者</th>
                                <td>{{ $user->reviewedBy?->name ?? '未設定' }}</td>
                            </tr>
                            <tr>
                                <th scope="row">最終審査日時</th>
                                <td>
                                    @if ($user->reviewed_at)
                                        <time datetime="{{ $user->reviewed_at->toIso8601String() }}">{{ $user->reviewed_at->format('Y-m-d H:i:s') }}</time>
                                    @else
                                        未実施
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">最終確認日時</th>
                                <td>
                                    @if ($user->verified_at)
                                        <time datetime="{{ $user->verified_at->toIso8601String() }}">{{ $user->verified_at->format('Y-m-d') }}</time>
                                    @else
                                        未確認
                                    @endif
                                </td>
                            </tr>
                            <tr><th scope="row">確認方法</th><td>{{ $user->verification_method ?? '未記録' }}</td></tr>
                            <tr><th scope="row">管理者用審査メモ</th><td class="contact-detail__message">{{ $user->verification_note ?? 'なし' }}</td></tr>
                        </tbody>
                    </table>
                </div>
                @if (auth()->id() !== $user->id)
                    <form class="admin-member-details__action" method="POST" action="{{ route('admin.members.status.update', $user) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $user->is_active ? 'suspended' : 'active' }}">
                        <button class="btn {{ $user->is_active ? 'btn--danger' : 'btn--primary' }}" type="submit">
                            {{ $user->is_active ? '利用停止' : '利用再開' }}
                        </button>
                    </form>
                @else
                    <p class="admin-member-details__action">ログイン中の管理者アカウントは利用停止できません。</p>
                @endif
            </section>

            <section class="admin-panel" aria-labelledby="admin-member-verification-title">
                <h2 id="admin-member-verification-title">会員確認・審査</h2>
                <p class="member-help">確認済みへ変更する場合は、実際に行った確認方法と確認日を記録してください。証明書番号・本人確認書類の画像は保存しません。</p>
                <form method="POST" action="{{ route('admin.members.verification.update', $user) }}" class="content-sections">
                    @csrf
                    @method('PATCH')
                    <div class="form-field">
                        <label for="verification-status">確認状態<span aria-hidden="true"> *</span></label>
                        <select id="verification-status" name="verification_status" required>
                            @foreach ($verificationStatuses as $verificationStatus)
                                <option value="{{ $verificationStatus->value }}" @selected(old('verification_status', $user->verification_status->value) === $verificationStatus->value)>
                                    {{ $verificationStatus->label() }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('verification_status'))
                            <p class="form-field__error">{{ $errors->first('verification_status') }}</p>
                        @endif
                    </div>
                    <div class="form-field">
                        <label for="verification-type">確認区分<span aria-hidden="true"> *</span></label>
                        <select id="verification-type" name="verification_type" required>
                            @foreach ($verificationTypes as $verificationType)
                                <option value="{{ $verificationType->value }}" @selected(old('verification_type', $user->verification_type?->value) === $verificationType->value)>
                                    {{ $verificationType->label() }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('verification_type'))
                            <p class="form-field__error">{{ $errors->first('verification_type') }}</p>
                        @endif
                    </div>
                    <div class="form-field">
                        <label for="verification-method">確認方法</label>
                        <input id="verification-method" name="verification_method" type="text" maxlength="255" value="{{ old('verification_method', $user->verification_method) }}" aria-describedby="verification-method-help">
                        <p class="form-field__help" id="verification-method-help">確認済みにする場合は、実際に行った方法を入力してください。証明書番号などの機密情報は入力しないでください。</p>
                        @if ($errors->has('verification_method'))
                            <p class="form-field__error">{{ $errors->first('verification_method') }}</p>
                        @endif
                    </div>
                    <div class="form-field">
                        <label for="verified-at">確認日</label>
                        <input id="verified-at" name="verified_at" type="date" value="{{ old('verified_at', $user->verified_at?->format('Y-m-d')) }}">
                        @if ($errors->has('verified_at'))
                            <p class="form-field__error">{{ $errors->first('verified_at') }}</p>
                        @endif
                    </div>
                    <div class="form-field">
                        <label for="verification-note">管理者用審査メモ</label>
                        <textarea id="verification-note" name="verification_note" rows="5" maxlength="10000">{{ old('verification_note', $user->verification_note) }}</textarea>
                        <p class="form-field__help">会員には公開されません。証明書番号などの機密情報は記載しないでください。</p>
                        @if ($errors->has('verification_note'))
                            <p class="form-field__error">{{ $errors->first('verification_note') }}</p>
                        @endif
                    </div>
                    <button class="btn btn--primary" type="submit">確認状況を保存</button>
                </form>
            </section>

            <section class="admin-panel" aria-labelledby="admin-member-verification-history-title">
                <h2 id="admin-member-verification-history-title">確認状態変更履歴</h2>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">日時</th>
                                <th scope="col">担当管理者</th>
                                <th scope="col">確認区分</th>
                                <th scope="col">状態変更</th>
                                <th scope="col">記録</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($verificationHistory as $history)
                                <tr>
                                    <td><time datetime="{{ $history->created_at->toIso8601String() }}">{{ $history->created_at->format('Y-m-d H:i:s') }}</time></td>
                                    <td>{{ $history->actor?->name ?? $history->actor_label ?? '削除済み管理者' }}</td>
                                    <td>{{ \App\Enums\MemberVerificationType::tryFrom($history->details['to_type'] ?? '')?->label() ?? '未設定' }}</td>
                                    <td>
                                        {{ \App\Enums\MemberVerificationStatus::tryFrom($history->details['from_status'] ?? '')?->label() ?? '不明' }}
                                        →
                                        {{ \App\Enums\MemberVerificationStatus::tryFrom($history->details['to_status'] ?? '')?->label() ?? '不明' }}
                                    </td>
                                    <td>
                                        @if ($history->details['verification_method_recorded'] ?? false)
                                            確認方法・確認日を記録
                                        @else
                                            審査状態を記録
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5">確認状態の変更履歴はありません。</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="admin-panel" aria-labelledby="admin-member-activity-title">
                <h2 id="admin-member-activity-title">この会員を対象とした管理操作履歴</h2>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">日時</th>
                                <th scope="col">実行者</th>
                                <th scope="col">操作</th>
                                <th scope="col">変更内容</th>
                                <th scope="col">IPアドレス</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activityLogs as $activity)
                                <tr>
                                    <td><time datetime="{{ $activity->created_at->toIso8601String() }}">{{ $activity->created_at->format('Y-m-d H:i:s') }}</time></td>
                                    <td>{{ $activity->actor?->email ?? $activity->actor_label ?? '削除済み会員' }}</td>
                                    <td>{{ match ($activity->action) {
                                        'member.suspended' => '会員停止',
                                        'member.activated' => '会員再開',
                                        'member.verification_updated' => '会員確認状況更新',
                                        'admin.granted' => '管理者権限付与',
                                        'admin.revoked' => '管理者権限解除',
                                        default => $activity->action,
                                    } }}</td>
                                    <td>{{ ($activity->details['from'] ?? null) }}{{ isset($activity->details['from'], $activity->details['to']) ? ' → ' : '' }}{{ ($activity->details['to'] ?? '') }}</td>
                                    <td>{{ $activity->ip_address ?? 'CLI' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5">この会員を対象とした管理操作履歴はありません。</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <p><a class="btn btn--outline" href="{{ route('admin.dashboard') }}">会員一覧へ戻る</a></p>
        </div>
    </section>
@endsection
