@props(['title', 'description'])

<article class="feature">
    <span class="feature__icon" aria-hidden="true">{{ $icon }}</span>
    <h3>{{ $title }}</h3>
    <p>{{ $description }}</p>
</article>
