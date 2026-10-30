@extends('layouts.app')

@section('title', 'お問い合わせ管理｜GunMatch')

@section('content')
    <section class="page-section">
        <div class="container">
            <x-page-heading
                title="お問い合わせ管理"
                description="サイト運営へのお問い合わせを確認します。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => '管理者ページ', 'route' => 'admin.dashboard'], ['label' => 'お問い合わせ管理']]"
            />
            <p><a class="btn btn--outline" href="{{ route('admin.dashboard') }}">管理者ページへ戻る</a></p>
            <section class="admin-panel" aria-labelledby="admin-inquiries-title">
                <h2 id="admin-inquiries-title">お問い合わせ一覧</h2>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">受付日時</th>
                                <th scope="col">送信者</th>
                                <th scope="col">件名</th>
                                <th scope="col">対応状態</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($inquiries as $inquiry)
                                <tr>
                                    <td><time datetime="{{ $inquiry->created_at->toIso8601String() }}">{{ $inquiry->created_at->format('Y-m-d H:i') }}</time></td>
                                    <td>{{ $inquiry->name }}<br>{{ $inquiry->email }}</td>
                                    <td><a href="{{ route('admin.contact-inquiries.show', $inquiry) }}">{{ $inquiry->subject }}</a></td>
                                    <td>{{ ['pending' => '未対応', 'in_progress' => '対応中', 'completed' => '対応済み'][$inquiry->status] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4">お問い合わせはありません。</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $inquiries->links() }}
            </section>
        </div>
    </section>
@endsection
