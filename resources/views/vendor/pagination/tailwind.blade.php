@use('App\Support\Localization')

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-4">
        <p class="hidden text-sm text-stone-600 sm:block">
            {!! __('Showing') !!}
            @if ($paginator->firstItem())
                <span class="font-semibold text-stone-900 tabular-nums">{{ Localization::digits($paginator->firstItem()) }}</span>
                {!! __('to') !!}
                <span class="font-semibold text-stone-900 tabular-nums">{{ Localization::digits($paginator->lastItem()) }}</span>
            @else
                {{ Localization::digits($paginator->count()) }}
            @endif
            {!! __('of') !!}
            <span class="font-semibold text-stone-900 tabular-nums">{{ Localization::digits($paginator->total()) }}</span>
            {!! __('results') !!}
        </p>

        <div class="flex w-full items-center justify-between gap-1.5 sm:w-auto sm:justify-end">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="grid size-10 cursor-not-allowed place-items-center rounded-full border border-stone-200 bg-white text-stone-300" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    <x-icon name="arrow-left" class="size-4 rtl:rotate-180" />
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="grid size-10 place-items-center rounded-full border border-stone-300 bg-white text-stone-700 transition hover:border-stone-900 hover:text-stone-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-stone-900" aria-label="{{ __('pagination.previous') }}">
                    <x-icon name="arrow-left" class="size-4 rtl:rotate-180" />
                </a>
            @endif

            {{-- Pagination Elements --}}
            <span class="flex items-center gap-1.5">
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="hidden size-10 place-items-center text-sm text-stone-400 sm:grid" aria-disabled="true">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="grid size-10 place-items-center rounded-full bg-stone-900 text-sm font-semibold text-white tabular-nums" aria-current="page">{{ Localization::digits($page) }}</span>
                            @else
                                <a href="{{ $url }}" class="hidden size-10 place-items-center rounded-full text-sm font-medium text-stone-700 tabular-nums transition hover:bg-stone-200/60 hover:text-stone-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-stone-900 sm:grid" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ Localization::digits($page) }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </span>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="grid size-10 place-items-center rounded-full border border-stone-300 bg-white text-stone-700 transition hover:border-stone-900 hover:text-stone-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-stone-900" aria-label="{{ __('pagination.next') }}">
                    <x-icon name="arrow-right" class="size-4 rtl:rotate-180" />
                </a>
            @else
                <span class="grid size-10 cursor-not-allowed place-items-center rounded-full border border-stone-200 bg-white text-stone-300" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    <x-icon name="arrow-right" class="size-4 rtl:rotate-180" />
                </span>
            @endif
        </div>
    </nav>
@endif
