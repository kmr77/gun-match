@extends('layouts.app')

@section('title', 'メールアドレスの確認｜GunMatch')

@section('content')
    <section class="auth-page">
        <div class="container auth-page__inner">
            <x-page-heading
                title="メールアドレスの確認"
                description="会員登録を続けるため、登録したメールアドレスをご確認ください。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => 'メールアドレスの確認']]"
            />
            <div class="auth-card">
                @if (session('status'))
                    <x-feedback-message type="success" title="お知らせ" :message="session('status')" />
                @endif
                <p class="page-notice" role="note">
                    確認メールは現在のメール配信設定に従って送信されます。メールが届かない場合は、再送信をお試しください。
                </p>
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button class="btn btn--primary auth-card__button" type="submit">確認メールを再送信</button>
                </form>
                <p class="member-help"><a href="{{ route('mypage') }}">マイページへ</a></p>
            </div>
        </div>
    </section>
@endsection
