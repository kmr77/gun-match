<footer class="site-footer">
    <div class="container site-footer__inner">
        <div class="site-footer__brand">
            <a href="{{ route('home') }}" aria-label="GunMatch ホーム">
                <img src="{{ asset('assets/img/logo/logo.png') }}" alt="GunMatch" width="2172" height="724" loading="lazy">
            </a>
            <p>狩猟の道具と人をつなぐ。</p>
            <small>狩猟を楽しむ人々に寄り添い、法令と安全を大切にするGunMatch。</small>
        </div>
        <nav class="site-footer__links" aria-label="フッターナビゲーション">
            <div>
                <h2>GunMatch</h2>
                <a href="{{ route('home') }}#categories">カテゴリー</a>
                <a href="{{ route('categories.index') }}">カテゴリー一覧</a>
                <a href="{{ route('home') }}#reasons">選ばれる理由</a>
                <a href="{{ route('home') }}#howto">利用の流れ</a>
                <a href="{{ route('about') }}">GunMatchについて</a>
                <a href="{{ route('guide') }}">初めての方・利用ガイド</a>
            </div>
            <div>
                <h2>ご利用案内</h2>
                <a href="{{ route('safety') }}">安全・法令について</a>
                <a href="{{ route('prohibited-items') }}">禁止事項・禁止品目</a>
                <a href="{{ route('faq') }}">よくある質問</a>
                <a href="{{ route('contact') }}">お問い合わせ案内</a>
                <a href="{{ route('terms') }}">利用規約</a>
                <a href="{{ route('privacy') }}">プライバシーポリシー</a>
            </div>
            <div>
                <h2>会員メニュー</h2>
                <a href="{{ route('login') }}">ログイン</a>
                <a href="{{ route('register') }}">新規会員登録</a>
                <a href="{{ route('mypage') }}">マイページ</a>
            </div>
        </nav>
    </div>
    <div class="container site-footer__bottom">
        <small>© {{ date('Y') }} GunMatch</small>
        <a href="#page-top">ページの先頭へ <span aria-hidden="true">↑</span></a>
    </div>
</footer>
