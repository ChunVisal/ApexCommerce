{{-- resources/views/components/slide-over.blade.php --}}
@props(['name' => 'open'])

<div x-show="{{ $name }}" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

    <div x-show="{{ $name }}" x-transition.opacity @click="{{ $name }} = false"
        class="absolute inset-0 bg-gray-900/40 dark:bg-black/60"></div>

    <div x-show="{{ $name }}" x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute right-0 top-0 h-full w-full max-w-md bg-white dark:bg-zinc-900 shadow-xl flex flex-col border-l border-gray-300 dark:border-zinc-800">

        {{ $slot }}

    </div>
</div>
