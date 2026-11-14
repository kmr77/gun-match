@extends('layouts.app')

@section('title', $user->name.'の公開プロフィール｜GunMatch')

@section('content')
    <section class="content-page">
        <div class="container content-page__inner">
            <x-page-heading
                title="会員公開プロフィール"
                description="会員が公開する基本情報を表示します。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => '公開プロフィール']]"
            />
            <article class="profile-preview-card" aria-label="会員公開プロフィール">
                <span class="profile-preview-card__label">{{ $user->member_type->label() }}</span>
                <h2>{{ $user->name }}</h2>
                <dl>
                    <div><dt>表示名</dt><dd>{{ $user->name }}</dd></div>
                    <div><dt>会員区分</dt><dd>{{ $user->member_type->label() }}</dd></div>
                </dl>
            </article>
            <p class="member-help">
                本名、生年月日、電話番号、メールアドレス、所持許可の自己申告、運営による確認審査の状態・メモは公開していません。
            </p>
        </div>
    </section>
@endsection
