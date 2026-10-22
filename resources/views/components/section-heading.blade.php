@props(['title', 'id', 'kicker' => null, 'badge' => null, 'badgeVariant' => null])

<div class="section-heading">
    <div @class(['section-heading__title-group' => $badge])>
        @if ($badge)
            <span class="section-heading__icon section-heading__icon--{{ $badgeVariant }}" aria-hidden="true">{{ $badge }}</span>
        @else
            <span class="section-heading__accent" aria-hidden="true"></span>
        @endif
        <h2 id="{{ $id }}">{{ $title }}</h2>
    </div>
    @if ($kicker)
        <span class="section-kicker">{{ $kicker }}</span>
    @endif
</div>
