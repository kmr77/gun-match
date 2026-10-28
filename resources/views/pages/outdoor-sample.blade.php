@extends('layouts.app')

@section('title', $item['title'].'｜アウトドア用品サンプル｜GunMatch')

@section('content')
    <section class="section listings-section sample-detail-page">
        <div class="container">
            <x-page-heading
                :title="$item['title']"
                description="共通詳細テンプレートの表示確認用ページです。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => 'サンプル一覧', 'route' => 'outdoor-samples.index'], ['label' => $item['title']]]"
            />

            <p class="page-notice" role="note">
                架空の表示サンプルです。実在の商品・在庫・募集ではありません。価格提示、購入・譲渡、問い合わせ、取引連絡、マッチング機能は提供していません。
            </p>

            <div class="sample-detail">
                <x-listing-card :item="$item" />
                <div class="sample-detail__description">
                    <h2>サンプル情報</h2>
                    <p>{{ $item['description'] }}</p>
                    <p>カテゴリー：{{ $item['category'] }}</p>
                    <a class="text-link" href="{{ route('outdoor-samples.index') }}">サンプル一覧へ戻る</a>
                </div>
            </div>
        </div>
    </section>
@endsection
