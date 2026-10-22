@props(['title', 'id', 'kicker' => null])

<div class="section-heading">
    <div>
        <span class="section-heading__accent" aria-hidden="true"></span>
        <h2 id="{{ $id }}">{{ $title }}</h2>
    </div>
    @if ($kicker)
        <span class="section-kicker">{{ $kicker }}</span>
    @endif
</div>
