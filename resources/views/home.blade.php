@extends('layouts.app')

@section('content')
    <section class="hero" aria-labelledby="hero-title">
        <div class="container hero__inner">
            <div class="hero__copy">
                <p class="hero__eyebrow">HUNTING &amp; SHOOTING MARKETPLACE</p>
                <h1 id="hero-title">狩猟の道具と人をつなぐ<br>マーケットプレイス</h1>
                <p class="hero__lead">狩猟の道具と人をつなぐ、GunMatch。</p>
            </div>
        </div>
        <span class="hero__note" aria-hidden="true">狩猟を、<br>もっと身近に。</span>
    </section>

    <section class="section topics-section" id="categories" aria-labelledby="categories-title">
        <div class="container">
            <x-section-heading
                title="カテゴリー"
                id="categories-title"
                kicker="HUNTING & SHOOTING"
            />
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

    <section class="section listings-section" id="offer-items" aria-labelledby="offer-items-title">
        <div class="container">
            <x-section-heading
                title="新着の「譲る」（売りたい）"
                id="offer-items-title"
                badge="譲"
                badge-variant="offer"
                kicker="仮データ"
            />
            <p class="demo-notice">一般的なアウトドア用品の仮データです。実在の商品・募集ではありません。</p>
            <div class="listing-grid">
                @foreach ($offerItems as $item)
                    <x-listing-card :item="$item" mode="offer" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="section listings-section listings-section--wanted" id="wanted-items" aria-labelledby="wanted-items-title">
        <div class="container">
            <x-section-heading
                title="新着の「探す」（買いたい）"
                id="wanted-items-title"
                badge="探"
                badge-variant="wanted"
                kicker="仮データ"
            />
            <p class="demo-notice">一般的なアウトドア用品の仮データです。実在の商品・募集ではありません。</p>
            <div class="listing-grid">
                @foreach ($wantedItems as $item)
                    <x-listing-card :item="$item" mode="wanted" />
                @endforeach
            </div>
        </div>
    </section>

    <section class="features" id="reasons" aria-labelledby="reasons-title">
        <div class="container">
            <h2 id="reasons-title">GunMatchが選ばれる理由</h2>
            <p class="features__lead">法令への配慮と安全な狩猟文化を大切にします。</p>
            <div class="features-grid">
                <x-feature-card title="法令を大切に" description="銃器等に関わる法令を理解し、遵守する姿勢を大切にします。">
                    <x-slot:icon>
                        <svg viewBox="0 0 40 40" fill="none">
                            <path d="M20 4 32 9v9c0 8-5 14-12 18C13 32 8 26 8 18V9l12-5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="m14 20 4 4 8-9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </x-slot:icon>
                </x-feature-card>
                <x-feature-card title="情報をわかりやすく" description="道具や狩猟にまつわる情報を、正確で分かりやすく伝えることを目指します。">
                    <x-slot:icon>
                        <svg viewBox="0 0 40 40" fill="none">
                            <path d="M10 5h14l7 7v23H10V5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M24 5v8h7M15 20h11M15 25h11M15 30h7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </x-slot:icon>
                </x-feature-card>
                <x-feature-card title="安全を第一に" description="狩猟に関わるすべての場面で、安全と責任を最優先に考えます。">
                    <x-slot:icon>
                        <svg viewBox="0 0 40 40" fill="none">
                            <path d="M6 9h28v19H19l-8 6v-6H6V9Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <circle cx="14" cy="18.5" r="1.4" fill="currentColor"/>
                            <circle cx="20" cy="18.5" r="1.4" fill="currentColor"/>
                            <circle cx="26" cy="18.5" r="1.4" fill="currentColor"/>
                        </svg>
                    </x-slot:icon>
                </x-feature-card>
                <x-feature-card title="趣味を通じたつながり" description="同じ趣味を楽しむ人々の交流を大切にするブランドです。">
                    <x-slot:icon>
                        <svg viewBox="0 0 40 40" fill="none">
                            <circle cx="20" cy="13" r="5" stroke="currentColor" stroke-width="2"/>
                            <path d="M10 33v-3a10 10 0 0 1 20 0v3H10Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            <path d="M9 10a4 4 0 0 0 0 8M31 10a4 4 0 0 1 0 8M5 30v-2a7 7 0 0 1 5-6.7M35 30v-2a7 7 0 0 0-5-6.7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </x-slot:icon>
                </x-feature-card>
            </div>
        </div>
    </section>

    <section class="howto" id="howto" aria-labelledby="howto-title">
        <div class="container">
            <x-section-heading title="利用の流れ" id="howto-title" />
            <div class="howto__panel">
                <ol class="steps">
                    <x-flow-step number="1" title="GunMatchを知る" description="GunMatchのブランドや、大切にしている価値観をご紹介します。">
                        <x-slot:icon>
                            <svg class="step__icon" viewBox="0 0 40 40" fill="none" aria-hidden="true">
                                <circle cx="14" cy="12" r="6" stroke="currentColor" stroke-width="2"/>
                                <path d="M4 33v-3a10 10 0 0 1 20 0v3H4ZM30 12v12M24 18h12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </x-slot:icon>
                    </x-flow-step>
                    <x-flow-step number="2" title="法令を確認する" description="銃器等に関する法令や手続きは、公的機関の最新情報をご確認ください。">
                        <x-slot:icon>
                            <svg class="step__icon" viewBox="0 0 40 40" fill="none" aria-hidden="true">
                                <path d="M10 5h14l7 7v23H10V5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                <path d="M24 5v8h7M15 20h11M15 25h11M15 30h7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </x-slot:icon>
                    </x-flow-step>
                    <x-flow-step number="3" title="安全に楽しむ" description="狩猟を楽しむ際は、安全と責任を第一に行動してください。">
                        <x-slot:icon>
                            <svg class="step__icon" viewBox="0 0 40 40" fill="none" aria-hidden="true">
                                <path d="M6 9h28v19H19l-8 6v-6H6V9Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                <path d="M14 18h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </x-slot:icon>
                    </x-flow-step>
                </ol>
            </div>
            <p class="howto__legal" id="service-notice">銃器等の取り扱いには関係法令が適用されます。許可や手続きについては、最新の公的情報をご確認ください。</p>
        </div>
    </section>

    <section class="bottom-banner" aria-labelledby="bottom-banner-title">
        <div class="container bottom-banner__inner">
            <div class="bottom-banner__copy">
                <h2 id="bottom-banner-title">狩猟という共通の趣味が、新しい出会いをつくる。</h2>
                <p>狩猟を楽しむ人々に寄り添うGunMatch。</p>
            </div>
        </div>
    </section>
@endsection
