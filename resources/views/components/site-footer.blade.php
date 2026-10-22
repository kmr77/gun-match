<footer class="site-footer">
    <div class="container site-footer__inner">
        <div class="site-footer__brand">
            <a href="{{ route('home') }}" aria-label="GunMatch ホーム">
                <img src="{{ asset('assets/img/logo/logo.png') }}" alt="GunMatch" width="2172" height="724" loading="lazy">
            </a>
            <p>狩猟の道具と人をつなぐ。</p>
            <small>GunMatchは、狩猟用銃器・銃身・関連用品のマッチングサービスとして構想されたプロジェクトです。</small>
        </div>
        <nav class="site-footer__links" aria-label="フッターナビゲーション">
            <div>
                <h2>GunMatch</h2>
                <a href="{{ route('home') }}#categories">カテゴリー</a>
                <a href="{{ route('home') }}#reasons">選ばれる理由</a>
                <a href="{{ route('home') }}#howto">利用の流れ</a>
                <a href="{{ route('home') }}#service-notice">サービスについて</a>
            </div>
        </nav>
    </div>
    <div class="container site-footer__bottom">
        <small>© {{ date('Y') }} GunMatch</small>
        <a href="#page-top">ページの先頭へ <span aria-hidden="true">↑</span></a>
    </div>
</footer>
