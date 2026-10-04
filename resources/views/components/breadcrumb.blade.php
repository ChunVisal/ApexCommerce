@props(['breadcrumbs' => []])

@php
    $currentUrl = url()->current();
    $keys = array_keys($breadcrumbs);
    $hasCurrent = in_array($currentUrl, $keys, true);
    // position of the page you are on; anything after it is an "old page" you came back from
    $currentIndex = $hasCurrent ? array_search($currentUrl, $keys, true) : count($keys) - 1;
@endphp

@if (count($breadcrumbs) > 0)
    <nav aria-label="Breadcrumb" class="mb-2">
        <ol class="flex flex-wrap items-center text-xs">
            @foreach ($breadcrumbs as $url => $label)
                @php
                    $isCurrent = $loop->index === $currentIndex;
                    $afterCurrent = $loop->index > $currentIndex;
                @endphp
                <li class="flex items-center gap-1 min-w-0">
                    @if (!$loop->first)
                        @if ($afterCurrent)
                            <span class="px-1 font-bold text-gray-400 dark:text-zinc-500">:</span>
                        @else
                            <x-heroicon-o-chevron-right class="w-3 h-3 shrink-0 text-gray-300 dark:text-zinc-600"
                                stroke-width="2" />
                        @endif
                    @endif

                    @if (!$isCurrent)
                        <a href="{{ $url }}"
                            class="flex items-center gap-1.5 px-2 py-1 rounded-md font-medium text-gray-500 dark:text-zinc-400 hover:text-[#0F6E8C] dark:hover:text-sky-400 transition-colors">
                            @if ($loop->first)
                                <x-heroicon-o-home class="w-3.5 h-3.5 shrink-0" stroke-width="2" />
                            @endif
                            <span class="truncate max-w-[140px] sm:max-w-none">{{ $label }}</span>
                        </a>
                    @else
                        <span aria-current="page"
                            class="px-2 py-1 rounded-md font-semibold text-gray-900 dark:text-zinc-100 bg-gray-200/70 dark:bg-zinc-800/60 truncate max-w-[160px] sm:max-w-none">
                            {{ $label }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
