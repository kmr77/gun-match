@extends('layouts.app')

@section('title', $page['title'].'｜GunMatch')

@section('content')
    <section class="content-page">
        <div class="container content-page__inner">
            <x-page-heading
                :title="$page['title']"
                :description="$page['description']"
                :breadcrumbs="[['label' => 'トップ', 'route' => 'home'], ['label' => $page['title']]]"
            />

            @if (isset($page['notice']))
                <p class="page-notice" role="note">{{ $page['notice'] }}</p>
            @endif

            <div class="content-sections">
                @foreach ($page['sections'] ?? [] as $section)
                    <section class="content-panel">
                        <h2>{{ $section['title'] }}</h2>
                        @foreach ($section['paragraphs'] ?? [] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                        @if (isset($section['items']))
                            <ul>
                                @foreach ($section['items'] as $item)
                                    <li>{{ $item }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                @endforeach

                @foreach ($page['faqs'] ?? [] as $faq)
                    <details class="faq-item">
                        <summary>{{ $faq['question'] }}</summary>
                        <p>{{ $faq['answer'] }}</p>
                    </details>
                @endforeach
            </div>

            <a class="text-link content-page__back" href="{{ route('home') }}">GunMatchトップへ戻る</a>
        </div>
    </section>
@endsection
