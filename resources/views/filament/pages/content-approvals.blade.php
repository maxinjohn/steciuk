<x-filament-panels::page>
    @if ($this->pendingItems->isEmpty())
        <x-filament::section>
            <div class="py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                No pages, news, events, sermons, or gallery items are waiting for review.
            </div>
        </x-filament::section>
    @else
        <div class="space-y-4">
            @foreach ($this->pendingItems as $item)
                <x-filament::section>
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="min-w-0 space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800 dark:bg-amber-500/15 dark:text-amber-200">
                                    {{ $item['type'] }}
                                </span>
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-500/15 dark:text-gray-200">
                                    {{ $item['status'] }}
                                </span>
                            </div>
                            <h2 class="text-base font-semibold text-gray-950 dark:text-white">{{ $item['title'] }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Updated {{ $item['updated_at']->diffForHumans() }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <x-filament::button
                                tag="a"
                                href="{{ $item['edit_url'] }}"
                                color="gray"
                                icon="heroicon-o-pencil-square"
                            >
                                Review
                            </x-filament::button>

                            <x-filament::button
                                wire:click="approve(@js($item['model_class']), {{ $item['model_id'] }})"
                                wire:confirm="Approve and publish this item on the public site?"
                                color="success"
                                icon="heroicon-o-check-badge"
                            >
                                Approve & publish
                            </x-filament::button>

                            <x-filament::button
                                wire:click="returnToDraft(@js($item['model_class']), {{ $item['model_id'] }})"
                                wire:confirm="Return this item to draft for the editor to revise?"
                                color="warning"
                                icon="heroicon-o-arrow-uturn-left"
                            >
                                Return to draft
                            </x-filament::button>
                        </div>
                    </div>
                </x-filament::section>
            @endforeach
        </div>
    @endif
</x-filament-panels::page>
