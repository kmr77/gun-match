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
                        <x-form-field
                            :name="$field['name']"
                            :label="$field['label']"
                            :type="$field['type']"
                            :autocomplete="$field['autocomplete']"
                            :required="true"
                            :description="$field['description'] ?? null"
                            :options="$field['options'] ?? []"
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
                        <p class="form-consent">登録前に<a href="{{ route('terms') }}">利用規約</a>と<a href="{{ route('privacy') }}">プライバシーポリシー</a>をご確認ください。正式公開前に内容を確定します。</p>
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
