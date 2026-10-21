<footer class="site-footer">
    <div class="container site-footer__inner">
        <div class="site-footer__brand">
            <a href="{{ route('home') }}" aria-label="GunMatch ホーム">
                <img src="{{ asset('assets/img/logo/logo.png') }}" alt="GunMatch" width="2172" height="724" loading="lazy">
            </a>
            <p>自然を知る。安全に、山へ。</p>
            <small>狩猟・アウトドアについて学ぶための情報ガイドです。制度や現地の状況は、公的機関の最新の案内をご確認ください。</small>
        </div>
        <nav class="site-footer__links" aria-label="フッターナビゲーション">
            <div>
                <h2>情報ガイド</h2>
                <a href="{{ route('home') }}#hunting-system">狩猟制度</a>
                <a href="{{ route('home') }}#hunting-basics">猟法の基礎</a>
                <a href="{{ route('home') }}#categories">道具の種類</a>
                <a href="{{ route('home') }}#outdoor">アウトドアの準備</a>
            </div>
            <div>
                <h2>このサイトについて</h2>
                <a href="{{ route('home') }}#safety">安全とマナー</a>
                <a href="{{ route('home') }}#howto">ガイドの使い方</a>
                <a href="{{ route('home') }}#about">GunMatchについて</a>
            </div>
            <div>
                <h2>公的機関の情報</h2>
                <a href="https://www.env.go.jp/nature/choju/hunt/hunt2.html">環境省｜狩猟制度 <span aria-hidden="true">↗</span></a>
                <a href="https://www.env.go.jp/nature/nationalparks/about/manner/">環境省｜自然の中のマナー <span aria-hidden="true">↗</span></a>
                <a href="https://www.env.go.jp/nature/nationalparks/pick-up/long-trail/beginner/">環境省｜自然歩道ガイド <span aria-hidden="true">↗</span></a>
            </div>
        </nav>
    </div>
    <div class="container site-footer__bottom">
        <small>© {{ date('Y') }} GunMatch</small>
        <a href="#page-top">ページの先頭へ <span aria-hidden="true">↑</span></a>
    </div>
</footer>
