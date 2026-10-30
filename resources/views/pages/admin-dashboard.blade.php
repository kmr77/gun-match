@extends('layouts.app')

@section('title', '管理者ページ｜GunMatch')

@section('content')
    <section class="page-section">
        <div class="container">
            <x-page-heading
                title="管理者ページ"
                description="会員アカウントの検索・状態管理と、管理操作履歴を確認できます。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => '管理者ページ']]"
            />
            @if (session('status'))
                <x-feedback-message type="success" title="更新しました" :message="session('status')" />
            @endif
            @if ($errors->any())
                <x-feedback-message type="error" title="処理を完了できません" :message="$errors->first()" />
            @endif

            <section class="admin-panel" aria-labelledby="admin-members-title">
                <h2 id="admin-members-title">会員管理</h2>
                <form method="GET" action="{{ route('admin.dashboard') }}" class="admin-search">
                    <label for="member-search">会員名またはメールアドレス</label>
                    <div class="admin-search__controls">
                        <input id="member-search" name="search" type="search" value="{{ $search }}" maxlength="100">
                        <button class="btn btn--primary" type="submit">検索</button>
                        @if ($search !== '')
                            <a class="btn btn--outline" href="{{ route('admin.dashboard') }}">検索を解除</a>
                        @endif
                    </div>
                </form>

                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">会員名</th>
                                <th scope="col">メールアドレス</th>
                                <th scope="col">会員区分</th>
                                <th scope="col">登録日時</th>
                                <th scope="col">メール認証</th>
                                <th scope="col">アカウント状態</th>
                                <th scope="col">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($members as $member)
                                <tr>
                                    <td><a href="{{ route('admin.members.show', $member) }}">{{ $member->name }}</a></td>
                                    <td>{{ $member->email }}</td>
                                    <td>{{ $member->member_type->label() }}</td>
                                    <td><time datetime="{{ $member->created_at->toIso8601String() }}">{{ $member->created_at->format('Y-m-d H:i') }}</time></td>
                                    <td>{{ $member->hasVerifiedEmail() ? '認証済み' : '未認証' }}</td>
                                    <td>{{ $member->is_active ? '有効' : '停止中' }}</td>
                                    <td>
                                        @if (auth()->id() !== $member->id)
                                            <form method="POST" action="{{ route('admin.members.status.update', $member) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ $member->is_active ? 'suspended' : 'active' }}">
                                                <button class="btn {{ $member->is_active ? 'btn--danger' : 'btn--primary' }} btn--small" type="submit">
                                                    {{ $member->is_active ? '利用停止' : '利用再開' }}
                                                </button>
                                            </form>
                                        @else
                                            <span>ログイン中</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7">該当する会員はいません。</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $members->links() }}
            </section>

            <section class="admin-panel" aria-labelledby="admin-activity-title">
                <h2 id="admin-activity-title">管理操作履歴（直近20件）</h2>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">日時</th>
                                <th scope="col">実行者</th>
                                <th scope="col">対象会員</th>
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
                                    <td>{{ $activity->target?->email ?? ($activity->target_user_id ? '削除済み会員' : '—') }}</td>
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
                                <tr><td colspan="6">管理操作履歴はありません。</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </section>
@endsection
