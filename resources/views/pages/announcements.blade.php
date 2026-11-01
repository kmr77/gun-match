@extends('layouts.app')

@section('title', 'お知らせ｜GunMatch')

@section('content')
    <section class="content-page">
        <div class="container content-page__inner">
            <x-page-heading
                title="お知らせ"
                description="GunMatchからのお知らせをご案内します。"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => 'お知らせ']]"
            />
            <ul class="announcement-list">
                @forelse ($announcements as $announcement)
                    <li>
                        <time datetime="{{ $announcement->published_at->toIso8601String() }}">{{ $announcement->published_at->format('Y年m月d日 H:i') }}</time>
                        <h2><a href="{{ route('announcements.show', $announcement) }}">{{ $announcement->title }}</a></h2>
                        <p>{{ \Illuminate\Support\Str::limit($announcement->body, 180) }}</p>
                    </li>
                @empty
                    <li>現在公開中のお知らせはありません。</li>
                @endforelse
            </ul>
            {{ $announcements->links() }}
        </div>
    </section>
@endsection
