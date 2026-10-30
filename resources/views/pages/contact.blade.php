@extends('layouts.app')

@section('title', 'お問い合わせ｜GunMatch')

@section('content')
    <section class="content-page">
        <div class="container content-page__inner">
            <x-page-heading
                title="お問い合わせ"
                description="GunMatchのサイト運営に関するお問い合わせを受け付けています。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => 'お問い合わせ']]"
            />

            @if (session('status'))
                <x-feedback-message type="success" title="送信を受け付けました" :message="session('status')" />
            @else
                <p class="page-notice" role="note">会員間の交渉・取引メッセージには対応していません。</p>
                <form method="POST" action="{{ route('contact.store') }}" class="content-sections">
                    @csrf
                    @if ($errors->any())
                        <x-feedback-message type="error" title="入力内容をご確認ください" :message="$errors->first()" />
                    @endif
                    <x-form-field name="name" label="氏名" autocomplete="name" :required="true" :error="$errors->first('name')" />
                    <x-form-field name="email" label="メールアドレス" type="email" autocomplete="email" :required="true" :error="$errors->first('email')" />
                    <x-form-field name="subject" label="件名" :required="true" :error="$errors->first('subject')" />
                    <div class="form-field">
                        <label for="message">お問い合わせ内容<span aria-hidden="true"> *</span></label>
                        <textarea id="message" name="message" rows="8" maxlength="10000" required aria-invalid="{{ $errors->has('message') ? 'true' : 'false' }}" @if ($errors->has('message')) aria-describedby="message-error" @endif>{{ old('message') }}</textarea>
                        @if ($errors->has('message'))
                            <p class="form-field__error" id="message-error">{{ $errors->first('message') }}</p>
                        @endif
                    </div>
                    <button class="btn btn--primary" type="submit">お問い合わせを送信</button>
                </form>
            @endif
        </div>
    </section>
@endsection
