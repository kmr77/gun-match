@props(['name', 'label', 'type' => 'text', 'autocomplete' => null, 'required' => false])

<div class="form-field">
    <label for="{{ $name }}">{{ $label }}@if ($required)<span aria-hidden="true"> *</span>@endif</label>
    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if ($required) required @endif
    >
</div>
