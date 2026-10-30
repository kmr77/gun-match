@extends('layouts.app')

@section('title', 'お問い合わせ詳細｜GunMatch')

@section('content')
    <section class="page-section">
        <div class="container">
            <x-page-heading
                title="お問い合わせ詳細"
                description="サイト運営へのお問い合わせ内容を確認します。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => 'お問い合わせ管理', 'route' => 'admin.contact-inquiries.index'], ['label' => 'お問い合わせ詳細']]"
            />
            @if (session('status'))
                <x-feedback-message type="success" title="更新しました" :message="session('status')" />
            @endif
            @if ($errors->any())
                <x-feedback-message type="error" title="処理を完了できません" :message="$errors->first()" />
            @endif
            <section class="admin-panel" aria-labelledby="inquiry-detail-title">
                <h2 id="inquiry-detail-title">{{ $contactInquiry->subject }}</h2>
                <dl class="contact-detail">
                    <div><dt>受付日時</dt><dd><time datetime="{{ $contactInquiry->created_at->toIso8601String() }}">{{ $contactInquiry->created_at->format('Y-m-d H:i:s') }}</time></dd></div>
                    <div><dt>氏名</dt><dd>{{ $contactInquiry->name }}</dd></div>
                    <div><dt>メールアドレス</dt><dd>{{ $contactInquiry->email }}</dd></div>
                    <div><dt>お問い合わせ内容</dt><dd class="contact-detail__message">{{ $contactInquiry->message }}</dd></div>
                    <div><dt>現在の対応状態</dt><dd>{{ ['pending' => '未対応', 'in_progress' => '対応中', 'completed' => '対応済み'][$contactInquiry->status] }}</dd></div>
                    @if ($contactInquiry->responded_at)
                        <div><dt>対応日時</dt><dd><time datetime="{{ $contactInquiry->responded_at->toIso8601String() }}">{{ $contactInquiry->responded_at->format('Y-m-d H:i:s') }}</time></dd></div>
                    @endif
                </dl>
                <form method="POST" action="{{ route('admin.contact-inquiries.update', $contactInquiry) }}" class="admin-search">
                    @csrf
                    @method('PATCH')
                    <label for="inquiry-status">対応状態</label>
                    <div class="admin-search__controls">
                        <select id="inquiry-status" name="status" required>
                            <option value="pending" @selected(old('status', $contactInquiry->status) === 'pending')>未対応</option>
                            <option value="in_progress" @selected(old('status', $contactInquiry->status) === 'in_progress')>対応中</option>
                            <option value="completed" @selected(old('status', $contactInquiry->status) === 'completed')>対応済み</option>
                        </select>
                        <button class="btn btn--primary" type="submit">状態を更新</button>
                    </div>
                </form>
            </section>
            <p><a class="btn btn--outline" href="{{ route('admin.contact-inquiries.index') }}">お問い合わせ一覧へ戻る</a></p>
        </div>
    </section>
@endsection
