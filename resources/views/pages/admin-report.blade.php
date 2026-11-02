@extends('layouts.app')

@section('title', '通報詳細｜GunMatch')

@section('content')
    <section class="page-section">
        <div class="container">
            <x-page-heading
                title="通報詳細"
                description="通報内容と運営の対応状況を確認します。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => '通報管理', 'route' => 'admin.reports.index'], ['label' => '通報詳細']]"
            />
            @if (session('status'))
                <x-feedback-message type="success" title="対応を記録しました" :message="session('status')" />
            @endif
            @if ($errors->any())
                <x-feedback-message type="error" title="入力内容をご確認ください" :message="$errors->first()" />
            @endif
            <section class="admin-panel" aria-labelledby="report-detail-title">
                <h2 id="report-detail-title">通報 #{{ $report->id }}</h2>
                <dl class="contact-detail">
                    <div><dt>受付日時</dt><dd><time datetime="{{ $report->created_at->toIso8601String() }}">{{ $report->created_at->format('Y-m-d H:i:s') }}</time></dd></div>
                    <div>
                        <dt>通報者</dt>
                        <dd>
                            @if ($report->reporter)
                                <a href="{{ route('admin.members.show', $report->reporter) }}">{{ $report->reporter->name }}</a>
                                （{{ $report->reporter->email }}）
                            @else
                                削除済み会員
                            @endif
                        </dd>
                    </div>
                    <div><dt>通報の種類</dt><dd>{{ \App\Models\AbuseReport::TYPE_LABELS[$report->type] }}</dd></div>
                    <div><dt>通報対象の情報</dt><dd>{{ $report->target_information }}</dd></div>
                    <div><dt>通報理由・詳細</dt><dd class="contact-detail__message">{{ $report->details }}</dd></div>
                    <div><dt>現在の対応状態</dt><dd>{{ \App\Models\AbuseReport::STATUS_LABELS[$report->status] }}</dd></div>
                    @if ($report->handled_at)
                        <div><dt>最終対応日時</dt><dd><time datetime="{{ $report->handled_at->toIso8601String() }}">{{ $report->handled_at->format('Y-m-d H:i:s') }}</time></dd></div>
                        <div><dt>担当管理者</dt><dd>{{ $report->handler?->name ?? 'アカウント削除済み' }}</dd></div>
                    @endif
                </dl>
                <form method="POST" action="{{ route('admin.reports.update', $report) }}" class="content-sections">
                    @csrf
                    @method('PATCH')
                    <div class="form-field">
                        <label for="report-status">対応状態<span aria-hidden="true"> *</span></label>
                        <select id="report-status" name="status" required>
                            @foreach (\App\Models\AbuseReport::STATUS_LABELS as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $report->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="admin-note">管理者用対応メモ</label>
                        <textarea id="admin-note" name="admin_note" rows="6" maxlength="10000">{{ old('admin_note', $report->admin_note) }}</textarea>
                        <p class="form-field__help">このメモは管理者のみが閲覧できます。</p>
                    </div>
                    <button class="btn btn--primary" type="submit">対応内容を記録</button>
                </form>
            </section>
            <p><a class="btn btn--outline" href="{{ route('admin.reports.index') }}">通報一覧へ戻る</a></p>
        </div>
    </section>
@endsection
