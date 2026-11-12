@extends('layouts.app')

@section('title', $page['title'].'｜GunMatch')

@section('content')
    <section class="auth-page">
        <div class="container auth-page__inner">
            <x-page-heading
                :title="$page['title']"
                :description="$page['description']"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => $page['title']]]"
            />
            <div class="auth-card">
                <p class="page-notice" role="note">{{ $page['notice'] }}</p>
                @if (session('status'))
                    <x-feedback-message type="success" title="処理が完了しました" :message="session('status')" />
                @endif
                @if (session('mail_delivery_error'))
                    <x-feedback-message type="error" title="メールを送信できませんでした" :message="session('mail_delivery_error')" />
                @endif
                @if ($errors->any())
                    <x-feedback-message type="error" title="入力内容をご確認ください" message="各項目のエラーを修正して、もう一度送信してください。" />
                @endif
                @php
                    $action = match ($pageKey) {
                        'login' => route('login'),
                        'register' => route('register'),
                        'forgot-password' => route('password.email'),
                        'reset-password' => route('password.update'),
                    };
                @endphp
                <form class="preview-form" method="POST" action="{{ $action }}">
                    @csrf
                    @if ($pageKey === 'reset-password')
                        <input type="hidden" name="token" value="{{ $token }}">
                    @endif
                    @foreach ($page['fields'] as $field)
                        @php
                            $options = $field['name'] === 'prefecture'
                                ? $prefectures
                                : ($field['options'] ?? []);
                        @endphp
                        <x-form-field
                            :name="$field['name']"
                            :label="$field['label']"
                            :type="$field['type']"
                            :autocomplete="$field['autocomplete']"
                            :required="$field['required'] ?? true"
                            :description="$field['description'] ?? null"
                            :options="$options"
                            :value="$field['name'] === 'email' ? ($email ?? null) : null"
                            :error="$errors->first($field['name'])"
                        />
                    @endforeach

                    @if ($pageKey === 'login')
                        <label class="remember-field">
                            <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                            <span>ログイン状態を保持する</span>
                        </label>
                    @endif

                    @if ($pageKey === 'register')
                        <p class="form-consent">
                            <a href="{{ route('terms') }}">利用規約</a>と<a href="{{ route('privacy') }}">プライバシーポリシー</a>をご確認ください。
                            プライバシーポリシーの閲覧と利用規約への同意は別の手続きとして扱います。
                        </p>
                        @if ($termsConsentVersion !== null)
                            <label class="remember-field">
                                <input
                                    id="terms_version"
                                    type="checkbox"
                                    name="terms_version"
                                    value="{{ $termsConsentVersion }}"
                                    required
                                    @if ($errors->has('terms_version')) aria-invalid="true" aria-describedby="terms_version-error" @endif
                                    @checked(old('terms_version') === $termsConsentVersion)
                                >
                                <span>利用規約（{{ $termsConsentVersion }}）に同意します</span>
                            </label>
                            @if ($errors->has('terms_version'))
                                <p class="form-field__error" id="terms_version-error">{{ $errors->first('terms_version') }}</p>
                            @endif
                        @else
                            <p class="page-notice" role="note">
                                利用規約・プライバシーポリシーは準備中です。現在、正式な規約への同意取得・記録は行っていません。
                            </p>
                        @endif
                    @endif

                    <button class="btn btn--primary auth-card__button" type="submit">{{ $page['button'] }}</button>
                </form>

                <nav class="auth-links" aria-label="会員メニュー">
                    @foreach ($page['links'] as $link)
                        <a href="{{ route($link['route']) }}">{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            </div>
        </div>
    </section>
@endsection
