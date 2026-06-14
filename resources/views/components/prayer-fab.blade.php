@props([
    'label' => 'Pray',
    'url' => '/prayer-request',
    'ariaLabel' => 'Submit a prayer request',
])

<a
    href="{{ url($url) }}"
    class="prayer-fab"
    data-prefetch-link
    aria-label="{{ $ariaLabel }}"
>
    <span class="prayer-fab__halo" aria-hidden="true"></span>
    <span class="prayer-fab__icon" aria-hidden="true">🙏</span>
    <span class="prayer-fab__label">{{ $label }}</span>
</a>
