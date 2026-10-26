@props(['name', 'label', 'type' => 'text', 'autocomplete' => null, 'required' => false, 'description' => null, 'options' => []])

<div class="form-field">
    <label for="{{ $name }}">{{ $label }}@if ($required)<span aria-hidden="true"> *</span>@endif</label>
    @if ($description)
        <p class="form-field__description" id="{{ $name }}-description">{{ $description }}</p>
    @endif
    @if ($type === 'select')
        <select
            id="{{ $name }}"
            name="{{ $name }}"
            @if ($description) aria-describedby="{{ $name }}-description" @endif
            @if ($required) required @endif
        >
            <option value="" selected disabled>選択してください</option>
            @foreach ($options as $value => $option)
                <option value="{{ $value }}">{{ $option }}</option>
            @endforeach
        </select>
    @else
        <input
            id="{{ $name }}"
            name="{{ $name }}"
            type="{{ $type }}"
            @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
            @if ($description) aria-describedby="{{ $name }}-description" @endif
            @if ($required) required @endif
        >
    @endif
</div>
