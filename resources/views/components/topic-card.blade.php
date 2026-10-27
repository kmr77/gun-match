@props(['slug', 'name', 'image'])

<a class="topic-card" href="{{ route('categories.show', ['slug' => $slug]) }}" aria-label="{{ $name }}の案内">
    <div class="topic-card__media">
        <img src="{{ asset('assets/img/categories/' . $image) }}" alt="" width="240" height="178" loading="lazy">
    </div>
    <h3>{{ $name }}</h3>
</a>
