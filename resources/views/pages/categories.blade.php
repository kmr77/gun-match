@extends('layouts.app')

@section('title', 'カテゴリー一覧｜GunMatch')

@section('content')
    <section class="section topics-section">
        <div class="container">
            <x-page-heading
                title="カテゴリー一覧"
                description="GunMatchで想定しているカテゴリーの表示例です。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => 'カテゴリー一覧']]"
            />

            <p class="page-notice" role="note">
                現在、掲載・検索・譲渡・取得を支援する機能は提供していません。下記はページ構成の確認用で、掲載可能性や個別の適法性を示すものではありません。
            </p>

            <div class="category-grid" aria-label="カテゴリーの表示例">
                @foreach ($categories as $category)
                    <x-topic-card :slug="$category['slug']" :name="$category['name']" :image="$category['image']" />
                @endforeach
            </div>

            <p class="member-help">
                <a href="{{ route('safety') }}">安全・法令について</a>
                ・
                <a href="{{ route('prohibited-items') }}">禁止事項・禁止品目</a>
            </p>
        </div>
    </section>
@endsection
