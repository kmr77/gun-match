<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="狩猟の道具と人をつなぐGunMatch。カテゴリーや、法令と安全を大切にするブランドの考え方をご紹介します。">
    <title>@yield('title', 'GunMatch｜狩猟の道具と人をつなぐ')</title>
    <link rel="preload" as="image" href="{{ asset('assets/img/hero/hero-main.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/gunmatch.css') }}">
    <script src="{{ asset('assets/js/gunmatch.js') }}" defer></script>
</head>
<body class="brand-site" id="page-top">
    <a class="skip-link" href="#main-content">本文へ移動</a>
    <x-site-header />
    <main id="main-content" tabindex="-1">
        @yield('content')
    </main>
    <x-site-footer />
</body>
</html>
