@props(['number', 'title', 'description'])

<li class="step">
    <div class="step__heading">
        <span class="step__number">{{ $number }}</span>
        {{ $icon }}
        <h3>{{ $title }}</h3>
    </div>
    <p>{{ $description }}</p>
</li>
