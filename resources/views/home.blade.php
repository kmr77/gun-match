@extends('layouts.app')

@section('content')
    <section class="hero" aria-labelledby="hero-title">
        <div class="container hero__inner">
            <div class="hero__copy">
                <p class="hero__eyebrow">HUNTING &amp; SHOOTING MARKETPLACE</p>
                <h1 id="hero-title">狩猟の道具と人をつなぐ<br>マーケットプレイス</h1>
                <p class="hero__lead">狩猟用銃器・銃身・関連用品のマッチングサービスとして構想されたプロジェクトです。</p>
            </div>
        </div>
        <span class="hero__note" aria-hidden="true">狩猟を、<br>もっと身近に。</span>
    </section>

    <section class="section topics-section" id="categories" aria-labelledby="categories-title">
        <div class="container">
            <div class="section-heading">
                <div><span class="section-heading__accent" aria-hidden="true"></span><h2 id="categories-title">カテゴリー</h2></div>
                <span class="section-kicker">HUNTING &amp; SHOOTING</span>
            </div>
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

    <section class="features" id="reasons" aria-labelledby="reasons-title">
        <div class="container">
            <h2 id="reasons-title">GunMatchが選ばれる理由</h2>
            <p class="features__lead">狩猟を楽しむすべての人のために、安心・安全を大切にするサービスを構想しています。</p>
            <div class="features-grid">
                <article class="feature">
                    <span class="feature__icon" aria-hidden="true">
                        <svg viewBox="0 0 40 40" fill="none">
                            <path d="M20 4 32 9v9c0 8-5 14-12 18C13 32 8 26 8 18V9l12-5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="m14 20 4 4 8-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <h3>法令を尊重する</h3>
                    <p>銃器等の取り扱いに関わる法令を尊重し、責任ある姿勢を大切にします。</p>
                </article>
                <article class="feature">
                    <span class="feature__icon" aria-hidden="true">
                        <svg viewBox="0 0 40 40" fill="none">
                            <path d="M10 5h14l7 7v23H10V5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M24 5v8h7M15 20h11M15 25h11M15 30h7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <h3>情報を丁寧に伝える</h3>
                    <p>道具や狩猟にまつわる情報を、分かりやすく伝えることを目指します。</p>
                </article>
                <article class="feature">
                    <span class="feature__icon" aria-hidden="true">
                        <svg viewBox="0 0 40 40" fill="none">
                            <path d="M6 9h28v19H19l-8 6v-6H6V9Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <circle cx="14" cy="18.5" r="1.4" fill="currentColor"/>
                            <circle cx="20" cy="18.5" r="1.4" fill="currentColor"/>
                            <circle cx="26" cy="18.5" r="1.4" fill="currentColor"/>
                        </svg>
                    </span>
                    <h3>安全を大切にする</h3>
                    <p>安心して趣味を続けられるよう、安全への意識を大切にする構想です。</p>
                </article>
                <article class="feature">
                    <span class="feature__icon" aria-hidden="true">
                        <svg viewBox="0 0 40 40" fill="none">
                            <circle cx="20" cy="13" r="5" stroke="currentColor" stroke-width="2"/>
                            <path d="M10 33v-3a10 10 0 0 1 20 0v3H10Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M9 10a4 4 0 0 0 0 8M31 10a4 4 0 0 1 0 8M5 30v-2a7 7 0 0 1 5-6.7M35 30v-2a7 7 0 0 0-5-6.7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <h3>共通の趣味でつながる</h3>
                    <p>狩猟という共通の趣味を通じた、新しい出会いを目指しています。</p>
                </article>
            </div>
        </div>
    </section>

    <section class="howto" id="howto" aria-labelledby="howto-title">
        <div class="container">
            <div class="section-heading">
                <div>
                    <span class="section-heading__accent" aria-hidden="true"></span>
                    <h2 id="howto-title">利用の流れ</h2>
                </div>
            </div>
            <div class="howto__panel">
                <ol class="steps">
                    <li class="step">
                        <div class="step__heading">
                            <span class="step__number">1</span>
                            <svg class="step__icon" viewBox="0 0 40 40" fill="none" aria-hidden="true">
                                <circle cx="14" cy="12" r="6" stroke="currentColor" stroke-width="2"/>
                                <path d="M4 33v-3a10 10 0 0 1 20 0v3H4ZM30 12v12M24 18h12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <h3>構想を知る</h3>
                        </div>
                        <p>GunMatchが目指すサービスの考え方をご紹介します。</p>
                    </li>
                    <li class="step">
                        <div class="step__heading">
                            <span class="step__number">2</span>
                            <svg class="step__icon" viewBox="0 0 40 40" fill="none" aria-hidden="true">
                                <path d="M10 5h14l7 7v23H10V5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                <path d="M24 5v8h7M15 20h11M15 25h11M15 30h7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <h3>法令を確認する</h3>
                        </div>
                        <p>銃器等に関わる法令・手続きは、公的機関の最新情報をご確認ください。</p>
                    </li>
                    <li class="step">
                        <div class="step__heading">
                            <span class="step__number">3</span>
                            <svg class="step__icon" viewBox="0 0 40 40" fill="none" aria-hidden="true">
                                <path d="M6 9h28v19H19l-8 6v-6H6V9Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                <path d="M14 18h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <h3>安全を大切にする</h3>
                        </div>
                        <p>安全と責任を前提とした、狩猟の楽しみ方を大切にします。</p>
                    </li>
                </ol>
            </div>
            <p class="howto__legal" id="service-notice">GunMatchは、狩猟用銃器・銃身・関連用品を対象とするマッチングサービスとして構想されています。銃器等の取り扱いには関係法令の遵守が必要です。このページはデザイン確認用であり、掲載・検索・マッチング・問い合わせ・取引の機能はありません。</p>
        </div>
    </section>

    <section class="bottom-banner" aria-labelledby="bottom-banner-title">
        <img
            class="bottom-banner__image"
            src="{{ asset('assets/img/branding/banner-hunting.webp') }}"
            alt=""
            width="1254"
            height="1254"
            loading="lazy"
        >
        <div class="container bottom-banner__inner">
            <div class="bottom-banner__copy">
                <h2 id="bottom-banner-title">狩猟という共通の趣味が、新しい出会いをつくる。</h2>
                <p>GunMatchが目指すのは、趣味を通じたつながりです。</p>
            </div>
        </div>
    </section>
@endsection
