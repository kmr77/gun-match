@props(['type' => 'info', 'title', 'message'])

@php
    $role = $type === 'error' ? 'alert' : 'status';
@endphp

<div {{ $attributes->class(['feedback-message', "feedback-message--{$type}"]) }} role="{{ $role }}" aria-live="{{ $role === 'alert' ? 'assertive' : 'polite' }}">
    <span class="feedback-message__icon" aria-hidden="true">
        @switch($type)
            @case('success') ✓ @break
            @case('error') ! @break
            @default i
        @endswitch
    </span>
    <div>
        <h3>{{ $title }}</h3>
        <p>{{ $message }}</p>
    </div>
</div>
