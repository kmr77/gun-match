<header class="site-header">
    <div class="container site-header__inner">
        <a class="brand" href="{{ route('home') }}" aria-label="GunMatch ホーム">
            <img src="{{ asset('assets/img/logo/logo.png') }}" alt="GunMatch" width="2172" height="724">
        </a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" aria-label="メニューを開く" data-menu-toggle>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </button>
        <nav class="main-nav" id="primary-navigation" aria-label="メインナビゲーション" data-navigation>
            <a href="{{ route('home') }}#page-top">トップ</a>
            <a href="{{ route('home') }}#categories">カテゴリー</a>
            <a href="{{ route('home') }}#reasons">選ばれる理由</a>
            <a href="{{ route('home') }}#howto">利用の流れ</a>
            <a href="{{ route('safety') }}">法令・安全について</a>
        </nav>
    </div>
</header>
