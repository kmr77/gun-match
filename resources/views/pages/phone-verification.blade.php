@extends('layouts.app')

@section('title', '電話番号の確認｜GunMatch')

@section('content')
    <section class="auth-page">
        <div class="container auth-page__inner">
            <x-page-heading
                title="電話番号の確認"
                description="メール確認に続いて、SMSで電話番号を確認してください。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => '電話番号の確認']]"
            />
            <div class="auth-card">
                @if (session('status'))
                    <x-feedback-message type="success" title="お知らせ" :message="session('status')" />
                @endif
                <p class="page-notice" role="note">
                    確認対象：{{ auth()->user()->phone }}。電話番号の確認は、本人確認審査や所持許可の確認とは別です。
                </p>
                <p class="member-help">認証コードは5分間有効です。送信にはSMS事業者の設定が必要です。設定がない場合、電話番号は確認済みになりません。</p>
                @error('phone')
                    <p class="form-field__error" role="alert">{{ $message }}</p>
                @enderror
                <form method="POST" action="{{ route('phone-verification.send') }}">
                    @csrf
                    <button class="btn btn--primary auth-card__button" type="submit">認証コードを送信</button>
                </form>
                <form method="POST" action="{{ route('phone-verification.verify') }}">
                    @csrf
                    <x-form-field
                        name="code"
                        label="SMS認証コード（6桁）"
                        :required="true"
                        :error="$errors->first('code')"
                        type="tel"
                    />
                    <button class="btn btn--primary auth-card__button" type="submit">電話番号を確認</button>
                </form>
            </div>
        </div>
    </section>
@endsection
