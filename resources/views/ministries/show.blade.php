@extends('layouts.app')

@section('title', $ministry->name . ' | Ministries | ' . $siteName)
@section('description', $ministry->short_description ?? strip_tags($ministry->description))

@section('content')
    <article>
        <x-hero
            :title="$ministry->name"
            :subtitle="$ministry->short_description"
            :image="$ministry->featured_image"
            badge="Evangelical Episcopal"
            size="small"
        />
        <x-parish-action-strip class="!py-3" />

        <section class="py-12 sm:py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-3">
                    <div class="lg:col-span-2">
                        @if ($ministry->description)
                            <div class="prose-church">{!! safeHtml($ministry->description) !!}</div>
                        @endif
                    </div>

                    <aside class="space-y-6">
                        <x-card>
                            <h2 class="font-bold text-xl font-semibold text-ink">Ministry Details</h2>
                            <dl class="mt-4 space-y-4 text-sm">
                                @if ($ministry->meeting_time)
                                    <div>
                                        <dt class="font-medium text-ink-muted">Meeting Time</dt>
                                        <dd class="mt-1 text-ink">{{ $ministry->meeting_time }}</dd>
                                    </div>
                                @endif
                                @if ($ministry->contact_person)
                                    <div>
                                        <dt class="font-medium text-ink-muted">Contact Person</dt>
                                        <dd class="mt-1 text-ink">{{ $ministry->contact_person }}</dd>
                                    </div>
                                @endif
                                @if ($ministry->contact_email)
                                    <div>
                                        <dt class="font-medium text-ink-muted">Email</dt>
                                        <dd class="mt-1"><a href="mailto:{{ $ministry->contact_email }}" class="text-brand hover:underline">{{ $ministry->contact_email }}</a></dd>
                                    </div>
                                @endif
                            </dl>
                        </x-card>

                        <x-card>
                            <h2 class="font-bold text-xl font-semibold text-ink">Serve the Lord</h2>
                            <p class="mt-2 text-sm text-ink-muted">Every believer is called to serve Christ and His Church. If God is stirring your heart to join this ministry, we would love to hear from you.</p>
                            <p class="mt-3 text-xs font-semibold uppercase tracking-wider text-brand">Colossians 3:23–24</p>
                            <div class="mt-4">
                                @livewire('forms.volunteer-form')
                            </div>
                        </x-card>
                    </aside>
                </div>

                <div class="mt-10">
                    <x-button href="{{ route('ministries.index') }}" variant="outline">← Back to Ministries</x-button>
                </div>
            </div>
        </section>

        <x-scripture-ribbon
            text="Each of you should use whatever gift you have received to serve others, as faithful stewards of God's grace in its various forms."
            reference="1 Peter 4:10"
        />
    </article>
@endsection
