@props([
    'title',
    'context' => 'default',
    'actionHref' => null,
    'actionLabel' => null,
])

@php
    $comfort = \App\Support\ContextScripture::emptyStateFor($context);
@endphp

<div {{ $attributes->merge(['class' => 'feed-empty feed-empty--rich feed-empty--heavenly col-span-full']) }}>
    <span class="heavenly-empty__cross" aria-hidden="true">✝</span>
    <p class="feed-empty__title">{{ $title }}</p>
    <p class="feed-empty__text">{{ $slot }}</p>
    <blockquote class="heavenly-empty__verse">
        <p>&ldquo;{{ $comfort['text'] }}&rdquo;</p>
        <footer>{{ $comfort['ref'] }}</footer>
    </blockquote>
    @if ($actionHref && $actionLabel)
        <x-button :href="$actionHref" variant="outline" class="feed-empty__action">{{ $actionLabel }}</x-button>
    @endif
</div>
