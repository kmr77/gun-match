@extends('layouts.app')

@section('title', $category['name'].'｜カテゴリー｜GunMatch')

@section('content')
    <section class="section topics-section category-detail">
        <div class="container category-detail__inner">
            <x-page-heading
                :title="$category['name']"
                description="カテゴリーの基本情報をご案内します。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => 'カテゴリー一覧', 'route' => 'categories.index'], ['label' => $category['name']]]"
            />

            <p class="page-notice" role="note">
                このページはカテゴリー案内の表示確認用です。掲載募集、在庫、取得希望、問い合わせ、取引機能は提供していません。取り扱いにあたっては関係法令の確認・遵守が必要です。
            </p>

            <article class="category-detail__card" aria-labelledby="category-detail-title">
                <div class="category-detail__media">
                    <img
                        src="{{ asset('assets/img/categories/'.$category['image']) }}"
                        alt="{{ $category['name'] }}"
                        width="240"
                        height="178"
                    >
                </div>
                <div class="category-detail__copy">
                    <h2 id="category-detail-title">{{ $category['name'] }}</h2>
                    <p>GunMatchのカテゴリー情報です。掲載内容や利用可能な機能を案内するものではありません。</p>
                    <a class="text-link" href="{{ route('categories.index') }}">カテゴリー一覧へ戻る</a>
                </div>
            </article>

            <p class="member-help">
                <a href="{{ route('safety') }}">安全・法令について</a>
                ・
                <a href="{{ route('prohibited-items') }}">禁止事項・禁止品目</a>
            </p>
        </div>
    </section>
@endsection
