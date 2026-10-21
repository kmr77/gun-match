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
            <a href="{{ route('home') }}#hunting-system">狩猟制度</a>
            <a href="{{ route('home') }}#hunting-basics">猟法の基礎</a>
            <a href="{{ route('home') }}#categories">テーマ</a>
            <a href="{{ route('home') }}#outdoor">アウトドア</a>
            <a href="{{ route('home') }}#safety">安全とマナー</a>
            <a href="{{ route('home') }}#howto">ガイドの使い方</a>
        </nav>
        <a class="btn btn--primary btn--small header-guide-link" href="{{ route('home') }}#guide">情報ガイドを見る <span aria-hidden="true">→</span></a>
    </div>
</header>
