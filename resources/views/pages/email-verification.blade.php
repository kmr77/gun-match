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
                @if (session('mail_delivery_error'))
                    <x-feedback-message type="error" title="メールを送信できませんでした" :message="session('mail_delivery_error')" />
                @endif
                @if (auth()->user()->hasVerifiedEmail())
                    <p class="page-notice" role="status">メールアドレスは確認済みです。{{ auth()->user()->phone ? (auth()->user()->phone_verified_at ? '電話番号も確認済みです。' : '次にSMSで電話番号を確認してください。') : '次にプロフィールで電話番号を登録してください。' }}</p>
                    @if (! auth()->user()->phone)
                        <p><a class="btn btn--primary" href="{{ route('mypage.profile') }}">電話番号を登録する</a></p>
                    @elseif (! auth()->user()->phone_verified_at)
                        <p><a class="btn btn--primary" href="{{ route('phone-verification.notice') }}">電話番号の確認へ進む</a></p>
                    @else
                        <p><a class="btn btn--primary" href="{{ route('mypage') }}">マイページへ</a></p>
                    @endif
                @else
                    <p class="page-notice" role="note">
                        確認メールを開いてメールアドレスを確認してください。確認後、通常利用の前にSMSによる電話番号確認が必要です。
                    </p>
                    @if (app()->environment(['local', 'testing']) && config('mail.default') !== 'smtp')
                        <p class="member-help">現在の設定ではメールはテスト用メモリ内に保持され、実メールは送信されません。</p>
                    @else
                        <p class="member-help">メールが届かない場合は、迷惑メールフォルダーをご確認のうえ再送信してください。</p>
                    @endif
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button class="btn btn--primary auth-card__button" type="submit">確認メールを再送信</button>
                    </form>
                    <p class="member-help"><a href="{{ route('mypage.profile') }}">登録情報・電話番号を確認する</a></p>
                @endif
            </div>
        </div>
    </section>
@endsection
