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
