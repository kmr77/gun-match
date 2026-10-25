@extends('layouts.app')

@section('title', 'ページが見つかりません｜GunMatch')

@section('content')
    <section class="error-page">
        <div class="container error-page__inner">
            <p class="error-page__code">404</p>
            <h1>ページが見つかりません</h1>
            <p>お探しのページは移動または削除されたか、URLが正しくない可能性があります。</p>
            <a class="btn btn--primary" href="{{ route('home') }}">トップページへ戻る</a>
        </div>
    </section>
@endsection
