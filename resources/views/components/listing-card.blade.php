@props(['item', 'mode'])

<article class="listing-card">
    <div class="listing-card__media">
        <img
            src="{{ asset('assets/img/dummy/outdoor/' . $item['image']) }}"
            alt="{{ $item['alt'] }}"
            width="512"
            height="512"
            loading="lazy"
        >
        <span @class([
            'listing-card__kind',
            'listing-card__kind--offer' => $mode === 'offer',
            'listing-card__kind--wanted' => $mode === 'wanted',
        ])>{{ $mode === 'offer' ? '譲る' : '探す' }}</span>
        <span class="listing-card__status">仮データ</span>
    </div>
    <div class="listing-card__body">
        <span class="listing-card__type">{{ $item['category'] }}</span>
        <h3 class="listing-card__title">{{ $item['title'] }}</h3>
        <p class="listing-card__description">{{ $item['description'] }}</p>
    </div>
</article>
