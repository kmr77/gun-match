@extends('layouts.app')

@section('title', '通報管理｜GunMatch')

@section('content')
    <section class="page-section">
        <div class="container">
            <x-page-heading
                title="通報管理"
                description="会員から寄せられた通報を運営内で確認します。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => '管理者ページ', 'route' => 'admin.dashboard'], ['label' => '通報管理']]"
            />
            <p><a class="btn btn--outline" href="{{ route('admin.dashboard') }}">管理者ページへ戻る</a></p>
            <section class="admin-panel" aria-labelledby="admin-reports-title">
                <h2 id="admin-reports-title">通報一覧</h2>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">受付日時</th>
                                <th scope="col">通報者</th>
                                <th scope="col">種類</th>
                                <th scope="col">対象情報</th>
                                <th scope="col">対応状態</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reports as $report)
                                <tr>
                                    <td><time datetime="{{ $report->created_at->toIso8601String() }}">{{ $report->created_at->format('Y-m-d H:i') }}</time></td>
                                    <td>
                                        @if ($report->reporter)
                                            <a href="{{ route('admin.members.show', $report->reporter) }}">{{ $report->reporter->name }}</a>
                                        @else
                                            削除済み会員
                                        @endif
                                    </td>
                                    <td>{{ \App\Models\AbuseReport::TYPE_LABELS[$report->type] }}</td>
                                    <td><a href="{{ route('admin.reports.show', $report) }}">{{ $report->target_information }}</a></td>
                                    <td>{{ \App\Models\AbuseReport::STATUS_LABELS[$report->status] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5">通報はありません。</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $reports->links() }}
            </section>
        </div>
    </section>
@endsection
