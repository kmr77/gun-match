@extends('layouts.app')

@section('title', 'アウトドア用品サンプル一覧｜GunMatch')

@section('content')
    <section class="section listings-section sample-list-page">
        <div class="container">
            <x-page-heading
                title="アウトドア用品サンプル一覧"
                description="共通一覧テンプレートの表示確認用ページです。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => 'サンプル一覧']]"
            />

            <p class="page-notice" role="note">
                以下は一般的なアウトドア用品の架空サンプルです。実在の商品・在庫・募集ではありません。購入、譲渡、価格提示、問い合わせ、取引連絡、マッチング機能は提供していません。
            </p>

            <div class="listing-grid">
                @foreach ($items as $item)
                    <x-listing-card
                        :item="$item"
                        :href="route('outdoor-samples.show', ['slug' => $item['slug']])"
                    />
                @endforeach
            </div>
        </div>
    </section>
@endsection
