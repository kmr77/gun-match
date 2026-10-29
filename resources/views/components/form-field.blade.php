@props(['name', 'label', 'type' => 'text', 'autocomplete' => null, 'required' => false, 'description' => null, 'options' => [], 'value' => null, 'error' => null])

<div class="form-field">
    <label for="{{ $name }}">{{ $label }}@if ($required)<span aria-hidden="true"> *</span>@endif</label>
    @if ($description)
        <p class="form-field__description" id="{{ $name }}-description">{{ $description }}</p>
    @endif
    @if ($error)
        <p class="form-field__error" id="{{ $name }}-error">{{ $error }}</p>
    @endif
    @php
        $describedBy = collect([
            $description ? "{$name}-description" : null,
            $error ? "{$name}-error" : null,
        ])->filter()->implode(' ');
    @endphp
    @if ($type === 'select')
        <select
            id="{{ $name }}"
            name="{{ $name }}"
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($error) aria-invalid="true" @endif
            @if ($required) required @endif
        >
            <option value="" @selected(old($name, $value) === null || old($name, $value) === '') disabled>選択してください</option>
            @foreach ($options as $optionValue => $option)
                <option value="{{ $optionValue }}" @selected(old($name, $value) === (string) $optionValue)>{{ $option }}</option>
            @endforeach
        </select>
    @else
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @if ($type !== 'password') value="{{ old($name, $value) }}" @endif
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($error) aria-invalid="true" @endif
            @if ($required) required @endif
        >
    @endif
</div>
