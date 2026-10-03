@props(['breadcrumbs' => []])

@if (count($breadcrumbs) > 0)
    <nav aria-label="Breadcrumb" class="mb-2">
        <ol class="flex flex-wrap items-center gap-1 text-xs">
            @foreach ($breadcrumbs as $url => $label)
                <li class="flex items-center gap-1 min-w-0">
                    @if (!$loop->first)
                        <x-heroicon-o-chevron-right class="w-3 h-3 shrink-0 text-gray-400 dark:text-zinc-600"
                            stroke-width="2" />
                    @endif

                    @if (!$loop->last)
                        <a href="{{ $url }}"
                            class="flex items-center gap-1.5 px-2 py-1 rounded-md font-medium text-gray-500 dark:text-zinc-400  hover:text-[#0F6E8C] dark:hover:text-sky-500 transition-colors">
                            @if ($loop->first)
                                <x-heroicon-o-home class="w-3.5 h-3.5 shrink-0" stroke-width="2" />
                            @endif
                            <span class="truncate max-w-[140px] sm:max-w-none">{{ $label }}</span>
                        </a>
                    @else
                        <span aria-current="page"
                            class="px-2 py-1 rounded-md font-semibold text-gray-800 dark:text-zinc-100 bg-gray-200 dark:bg-zinc-800/60 truncate max-w-[160px] sm:max-w-none">
                            {{ $label }}
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
