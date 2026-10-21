@extends('layouts.app')

@section('content')
    <section class="hero" aria-labelledby="hero-title">
        <div class="container hero__inner">
            <div class="hero__copy">
                <p class="hero__eyebrow">GUNMATCH · HUNTING &amp; OUTDOOR GUIDE</p>
                <h1 id="hero-title">自然を知る。<br>安全に、山へ。</h1>
                <p class="hero__lead">狩猟のこと、山のこと、自然との向き合い方。<br class="desktop-break">学ぶことから、次の一歩をはじめよう。</p>
                <div class="hero__actions">
                    <a class="btn btn--primary" href="#guide">情報ガイドを見る <span aria-hidden="true">→</span></a>
                    <a class="hero__safety-link" href="#safety">安全とマナーを確認する <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </div>
    </section>

    <section class="section topics-section" id="categories" aria-labelledby="categories-title">
        <div class="container">
            <div class="section-heading">
                <div><span class="section-heading__accent" aria-hidden="true"></span><h2 id="categories-title">狩猟・アウトドアのテーマ</h2></div>
                <span class="section-kicker">INFORMATION CATEGORIES</span>
            </div>
            <p class="section-intro">道具の種類を知るための静的な案内です。出品・譲渡・取得・検索・問い合わせには対応していません。</p>
            <div class="topic-grid">
                @foreach ($categories as $category)
                    <x-topic-card
                        :name="$category['name']"
                        :image="$category['image']"
                    />
                @endforeach
            </div>
        </div>
    </section>

    <section class="section guide-section" id="guide" aria-labelledby="guide-title">
        <div class="container">
            <div class="section-heading">
                <div><span class="section-heading__accent" aria-hidden="true"></span><h2 id="guide-title">まずは、知ることから。</h2></div>
                <span class="section-kicker">LEARN THE BASICS</span>
            </div>
            <p class="section-intro">はじめての方にも、あらためて学びたい方にも。気になるテーマから、公的な情報へ。</p>
            <div class="guide-grid">
                <x-info-card
                    id="hunting-system" number="01" icon="badge-check" title="狩猟制度を知る"
                    description="狩猟免許や狩猟者登録、区域・期間など、制度の全体像を確認するための入り口です。詳しい条件は、環境省やお住まいの都道府県の案内をご確認ください。"
                    href="https://www.env.go.jp/nature/choju/hunt/hunt2.html" source="環境省の制度案内を読む"
                />
                <x-info-card
                    id="hunting-basics" number="02" icon="mail-check" title="猟法の基礎を学ぶ"
                    description="網猟・わな猟・銃猟など、狩猟にはさまざまな区分があります。まずは制度上の分類を知り、学習や安全講習について地域の担当窓口で確認しましょう。"
                    href="https://www.env.go.jp/nature/choju/hunt/hunt2.html" source="環境省で猟法の区分を知る"
                />
                <x-info-card
                    id="outdoor" number="03" icon="bell" title="山歩きの準備をする"
                    description="自然を楽しむ時間は、計画から始まります。体力に合うルート選び、服装や持ち物、天候の確認など、出かける前の準備を見直してみましょう。"
                    href="https://www.env.go.jp/nature/nationalparks/pick-up/long-trail/beginner/" source="環境省の自然歩道ガイドを読む"
                />
            </div>
            <p class="source-note">リンク先は環境省の公式サイトです。制度・地域の案内は最新情報をご確認ください。</p>
        </div>
    </section>

    <section class="features" id="safety" aria-labelledby="safety-title">
        <div class="container">
            <p class="section-kicker">SAFETY &amp; RESPECT</p>
            <h2 id="safety-title">山に入る前に、4つの確認。</h2>
            <p class="features__lead">自分の安全と、自然への思いやり。どちらも大切に。</p>
            <div class="features-grid">
                <article class="feature">
                    <span class="feature__icon"><img src="{{ asset('assets/icons/bell.svg') }}" alt="" width="33" height="33" loading="lazy"></span>
                    <h3>天候と現地の状況</h3>
                    <p>出発前に天気やルートの状況を確認。<br>無理のない計画を立てましょう。</p>
                </article>
                <article class="feature">
                    <span class="feature__icon"><img src="{{ asset('assets/icons/badge-check.svg') }}" alt="" width="33" height="33" loading="lazy"></span>
                    <h3>地域のルールとマナー</h3>
                    <p>立ち入りや施設利用の案内を確認。<br>地域の暮らしにも配慮を。</p>
                </article>
                <article class="feature">
                    <span class="feature__icon"><img src="{{ asset('assets/icons/mail-check.svg') }}" alt="" width="33" height="33" loading="lazy"></span>
                    <h3>服装と持ち物</h3>
                    <p>気温や行程に合う装備を準備。<br>出かける前に点検しましょう。</p>
                </article>
                <article class="feature">
                    <span class="feature__icon"><img src="{{ asset('assets/icons/message-circle.svg') }}" alt="" width="33" height="33" loading="lazy"></span>
                    <h3>行動計画の共有</h3>
                    <p>行き先や予定を家族などに共有。<br>安心して帰るための準備を。</p>
                </article>
            </div>
            <p class="features__source">参考：環境省 <a href="https://www.env.go.jp/nature/nationalparks/about/manner/">国立公園の利用上のマナー ↗</a> ／ <a href="https://www.env.go.jp/nature/nationalparks/pick-up/long-trail/beginner/">自然歩道初心者ガイド ↗</a></p>
        </div>
    </section>

    <section class="section howto" id="howto" aria-labelledby="howto-title">
        <div class="container">
            <div class="section-heading">
                <div><span class="section-heading__accent" aria-hidden="true"></span><h2 id="howto-title">このガイドの使い方</h2></div>
                <span class="section-kicker">YOUR FIRST STEP</span>
            </div>
            <ol class="steps">
                <li class="step"><span class="step__number" aria-hidden="true">1</span><div><h3>テーマを選ぶ</h3><p>制度のこと、自然の楽しみ方。知りたいテーマの案内から読み進めます。</p></div></li>
                <li class="step"><span class="step__number" aria-hidden="true">2</span><div><h3>公的な情報を読む</h3><p>各案内のリンクから、環境省が公開する制度や安全に関する情報を確認します。</p></div></li>
                <li class="step"><span class="step__number" aria-hidden="true">3</span><div><h3>地域の案内を確かめる</h3><p>実際に出かける前に、都道府県や現地施設が発信する最新の案内を確認します。</p></div></li>
            </ol>
        </div>
    </section>

    <section class="bottom-banner" id="about" aria-labelledby="about-title">
        <div class="container bottom-banner__inner">
            <div>
                <p class="bottom-banner__eyebrow">ABOUT GUNMATCH</p>
                <h2 id="about-title">自然との付き合いを、<br>知ることから。</h2>
                <p>GunMatchは、狩猟・アウトドアの基礎と安全を学ぶ情報ガイドです。</p>
            </div>
            <a class="btn btn--light" href="#guide">情報ガイドに戻る <span aria-hidden="true">↑</span></a>
        </div>
    </section>
@endsection
