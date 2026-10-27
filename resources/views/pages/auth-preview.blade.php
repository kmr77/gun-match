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
                <div class="preview-form" role="group" aria-label="{{ $page['title'] }}の入力表示例">
                    @foreach ($page['fields'] as $field)
                        <x-form-field
                            :name="$field['name']"
                            :label="$field['label']"
                            :type="$field['type']"
                            :autocomplete="$field['autocomplete']"
                            :required="true"
                            :description="$field['description'] ?? null"
                            :options="$field['options'] ?? []"
                        />
                    @endforeach

                    @if ($pageKey === 'register')
                        <p class="form-consent">登録画面の表示確認用です。<a href="{{ route('terms') }}">利用規約（準備中）</a>と<a href="{{ route('privacy') }}">プライバシーポリシー（準備中）</a>をご確認ください。</p>
                    @endif

                    <div class="form-error-slot" aria-live="polite">入力エラー表示領域（現在未実装）</div>
                    <button class="btn btn--primary auth-card__button" type="button" disabled>{{ $page['button'] }}</button>
                </div>

                <nav class="auth-links" aria-label="会員メニュー">
                    @foreach ($page['links'] as $link)
                        <a href="{{ route($link['route']) }}">{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            </div>
        </div>
    </section>
@endsection
