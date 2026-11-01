@extends('layouts.app')

@section('title', 'お知らせ管理｜GunMatch')

@section('content')
    <section class="page-section">
        <div class="container">
            <x-page-heading
                title="お知らせ管理"
                description="サイトに掲載するお知らせを作成・編集・公開します。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => '管理者ページ', 'route' => 'admin.dashboard'], ['label' => 'お知らせ管理']]"
            />
            @if (session('status'))
                <x-feedback-message type="success" title="更新しました" :message="session('status')" />
            @endif
            <p><a class="btn btn--primary" href="{{ route('admin.announcements.create') }}">お知らせを新規作成</a></p>
            <section class="admin-panel" aria-labelledby="admin-announcements-title">
                <h2 id="admin-announcements-title">お知らせ一覧</h2>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">タイトル</th>
                                <th scope="col">状態</th>
                                <th scope="col">公開日時</th>
                                <th scope="col">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($announcements as $announcement)
                                <tr>
                                    <td>{{ $announcement->title }}</td>
                                    <td>{{ $announcement->status === 'published' ? '公開' : '下書き' }}</td>
                                    <td>
                                        @if ($announcement->published_at)
                                            <time datetime="{{ $announcement->published_at->toIso8601String() }}">{{ $announcement->published_at->format('Y-m-d H:i') }}</time>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        <a class="btn btn--outline btn--small" href="{{ route('admin.announcements.edit', $announcement) }}">編集</a>
                                        <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn--danger btn--small" type="submit">削除</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4">お知らせはありません。</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $announcements->links() }}
            </section>
        </div>
    </section>
@endsection
