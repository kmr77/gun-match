@props(['id', 'number', 'icon', 'title', 'description', 'href', 'source'])

<article class="guide-card" id="{{ $id }}" aria-labelledby="{{ $id }}-title">
    <div class="guide-card__top">
        <span class="guide-card__number" aria-hidden="true">{{ $number }}</span>
        <img src="{{ asset('assets/icons/' . $icon . '.svg') }}" alt="" width="32" height="32" loading="lazy">
    </div>
    <h3 id="{{ $id }}-title">{{ $title }}</h3>
    <p>{{ $description }}</p>
    <a class="guide-card__link" href="{{ $href }}">{{ $source }} <span aria-hidden="true">↗</span></a>
</article>
