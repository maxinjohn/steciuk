@extends('layouts.app')

@section('title', $page->seo_title ?? $page->title . ' | ' . $siteName)
@section('description', $page->seo_description ?? strip_tags($page->content))

@section('content')
    <x-hero
        :title="$page->hero_title ?? $page->title"
        :subtitle="$page->hero_subtitle"
        :image="$page->featured_image"
        badge="Evangelical Oriental Protestant"
        size="small"
    />

    @if ($page->slug === 'our-church')
        <x-eauk-member-panel />
        <x-faith-pillars class="!py-6 sm:!py-8" />
    @else
        <x-parish-action-strip class="!py-3" />
    @endif

    @if ($page->content)
        <section class="page-section page-section--compact page-section--article">
            <div class="page-section-inner mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                @if ($page->slug === 'our-church')
                    <nav class="our-church-nav" aria-label="On this page">
                        <p class="our-church-nav__label">On this page</p>
                        <ul class="our-church-nav__list" role="list">
                            <li><a href="#who-we-are">Who we are</a></li>
                            <li><a href="#where-we-gather">Where we gather</a></li>
                            <li><a href="#evangelical-alliance">Evangelical Alliance</a></li>
                            <li><a href="#what-we-believe">What we believe</a></li>
                            <li><a href="#how-we-worship">How we worship</a></li>
                            <li><a href="#parish-life">Parish life</a></li>
                        </ul>
                    </nav>
                @endif

                <div @class([
                    'prose-church prose-church--page',
                    'prose-church--compact sm:text-center' => $page->slug === 'leadership',
                    'mt-8' => $page->slug === 'our-church',
                ])>{!! safeHtml($page->content) !!}</div>
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
