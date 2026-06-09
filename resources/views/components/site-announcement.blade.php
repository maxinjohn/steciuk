@if (($siteAnnouncementEnabled ?? false) && ($siteAnnouncementText ?? null))
    <div class="site-announcement" role="status">
        <div class="site-announcement-inner mx-auto flex max-w-7xl flex-col items-start gap-2 px-4 py-2.5 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <p class="text-sm font-medium text-ink">
                <span aria-hidden="true" class="mr-1.5">✝</span>
                {{ $siteAnnouncementText }}
            </p>
            @if ($siteAnnouncementLink ?? null)
                <a href="{{ $siteAnnouncementLink }}" class="site-announcement-link shrink-0 text-sm font-semibold text-brand underline-offset-2 hover:underline">
                    {{ $siteAnnouncementLinkLabel ?? 'Learn more' }} →
                </a>
            @endif
        </div>
    </div>
@endif
