@extends('layouts.app')

@section('title', $page->seo_title ?? $page->title . ' | ' . $siteName)
@section('description', $page->seo_description ?? strip_tags($page->content))

@section('content')
    <x-hero
        :title="$page->hero_title ?? $page->title"
        :subtitle="$page->hero_subtitle"
        :image="$page->featured_image"
        badge="Evangelical Episcopal"
        size="small"
    />
    <x-parish-action-strip class="!py-3" />
    <x-evangelical-trust-bar />

    @if ($page->slug === 'leadership')
        <x-leadership-grid :members="$leadershipMembers ?? collect()" />
    @endif

    @if ($page->content && $page->slug !== 'leadership')
        <section class="py-12 sm:py-16">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <div class="prose-church">{!! safeHtml($page->content) !!}</div>
            </div>
        </section>
    @elseif ($page->slug === 'leadership' && $page->content)
        <section class="py-8 sm:py-10">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <div class="prose-church prose-church--compact text-center">{!! safeHtml($page->content) !!}</div>
            </div>
        </section>
    @endif

    @if (in_array($page->slug, ['our-church', 'mission-vision', 'steci-heritage', 'welcome'], true))
        <x-scripture-ribbon
            :text="match ($page->slug) {
                'welcome' => 'Come to me, all you who are weary and burdened, and I will give you rest.',
                'our-church' => 'All Scripture is God-breathed and is useful for teaching, rebuking, correcting and training in righteousness.',
                'mission-vision' => 'Go and make disciples of all nations, baptizing them in the name of the Father and of the Son and of the Holy Spirit.',
                'steci-heritage' => 'We are therefore Christ\'s ambassadors, as though God were making his appeal through us.',
                default => 'Your word is a lamp to my feet and a light to my path.',
            }"
            :reference="match ($page->slug) {
                'welcome' => 'Matthew 11:28',
                'our-church' => '2 Timothy 3:16',
                'mission-vision' => 'Matthew 28:19',
                'steci-heritage' => '2 Corinthians 5:20',
                default => 'Psalm 119:105',
            }"
        />
    @endif

    @if ($page->contentBlocks->isNotEmpty())
        <x-content-blocks :blocks="$page->contentBlocks" />
    @endif
@endsection
