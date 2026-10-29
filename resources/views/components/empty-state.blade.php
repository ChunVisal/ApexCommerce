@props([
    'icon' => 'heroicon-o-cube',
    'title',
    'description' => null,
    'cta' => null,
    'action' => null,
    'href' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center py-[80px]']) }}>
    <div class="w-14 h-14 mb-4 bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center">
        <x-dynamic-component :component="$icon" class="w-7 h-7 text-gray-400 dark:text-zinc-500" />
    </div>  

    <h3 class="text-sm font-semibold uppercase text-gray-700 dark:text-zinc-300 mb-1">{{ $title }}</h3>

    @if ($description)
        <p class="text-xs text-gray-400 dark:text-zinc-500">{{ $description }}</p>
    @endif

    @if ($cta)
        @php
            $btnClass =
                'mt-4 inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-[#0F6E8C] rounded-md hover:bg-[#0c5972] transition';
        @endphp

        @if ($href)
            <a href="{{ $href }}" class="{{ $btnClass }}">
                <i class="fa-solid fa-plus text-[10px]"></i> {{ $cta }}
            </a>
        @else
            <button @click="{{ $action }}" class="{{ $btnClass }}">
                <i class="fa-solid fa-plus text-[10px]"></i> {{ $cta }}
            </button>
        @endif
    @endif
</div>
