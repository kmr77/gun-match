@extends('layouts.app')

@section('title', $announcement->title.'｜お知らせ｜GunMatch')

@section('content')
    <section class="content-page">
        <div class="container content-page__inner">
            <x-page-heading
                :title="$announcement->title"
                description="GunMatchからのお知らせ"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => 'お知らせ', 'route' => 'announcements.index'], ['label' => $announcement->title]]"
            />
            <article class="content-panel announcement-detail">
                <time datetime="{{ $announcement->published_at->toIso8601String() }}">{{ $announcement->published_at->format('Y年m月d日 H:i') }}</time>
                <p>{{ $announcement->body }}</p>
            </article>
            <a class="text-link content-page__back" href="{{ route('announcements.index') }}">お知らせ一覧へ戻る</a>
        </div>
    </section>
@endsection
