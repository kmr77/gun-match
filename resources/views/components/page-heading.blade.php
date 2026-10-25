@props(['title', 'description' => null, 'breadcrumbs' => []])

<header class="page-heading">
    @if ($breadcrumbs)
        <nav class="breadcrumbs" aria-label="パンくずリスト">
            @foreach ($breadcrumbs as $index => $breadcrumb)
                @if ($index > 0)
                    <span aria-hidden="true">/</span>
                @endif
                @if (isset($breadcrumb['route']))
                    <a href="{{ route($breadcrumb['route']) }}">{{ $breadcrumb['label'] }}</a>
                @else
                    <span aria-current="page">{{ $breadcrumb['label'] }}</span>
                @endif
            @endforeach
        </nav>
    @endif
    <h1>{{ $title }}</h1>
    @if ($description)
        <p>{{ $description }}</p>
    @endif
</header>
