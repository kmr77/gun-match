@props(['name', 'image'])

<article class="topic-card" aria-label="{{ $name }}">
    <div class="topic-card__media">
        <img src="{{ asset('assets/img/categories/' . $image) }}" alt="" width="240" height="178" loading="lazy">
    </div>
    <h3>{{ $name }}</h3>
</article>
