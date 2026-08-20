{{-- resources/views/components/sidebar-shell.blade.php --}}
<aside x-data="{
    open: localStorage.getItem('sidebar') !== 'closed',
    ready: false,
    toggle(val) {
        this.open = val;
        localStorage.setItem('sidebar', val ? 'open' : 'closed');
        document.documentElement.classList.toggle('sidebar-closed', !val);
    }
}" x-init="$nextTick(() => ready = true)"
    :class="[
        open ? 'w-[185px]' : 'w-14',
        ready ? 'transition-all duration-300' : ''
    ]"
    class="sticky top-[54px] h-[calc(100vh-55px)] flex flex-col bg-white dark:bg-black overflow-hidden z-40">

    <div class="absolute right-0 top-0 bottom-0 w-[1px] cursor-ew-resize hover:bg-blue-400 bg-gray-200 dark:bg-zinc-800 transition-all z-50"
        @mousedown.stop="
            let startX = $event.clientX;
            const onMove = (e) => {
                let diff = startX - e.clientX;
                if (diff > 30) toggle(false);
                else if (diff < -30) toggle(true);
            };
            const onUp = () => {
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
            };
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        ">
    </div>

    <div x-show="open" class="absolute top-1/3 -translate-y-1/2 right-0 z-50 flex items-center justify-center">
        <button @click="toggle(false)"
            class="w-6 h-12 bg-gray-100 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-800 rounded-l-lg shadow-sm flex items-center justify-center hover:bg-gray-200/30 dark:hover:bg-zinc-800/50 transition">
            <x-heroicon-o-chevron-left class="w-10 text-gray-600 dark:text-zinc-400" />
        </button>
    </div>

    <nav class="tab-container overflow-x-hidden flex-1 px-3 py-2 space-y-1 overflow-y-auto">
        <div x-show="!open" class="flex items-center px-3 pb-2 justify-center">
            <button @click="toggle(true)"
                class="flex items-center justify-center hover:bg-gray-200/30 dark:hover:bg-zinc-900/30 transition">
                <x-heroicon-o-chevron-right
                    class="w-7 bg-gray-200/50 dark:bg-zinc-800/50 rounded-sm p-1 text-gray-600 dark:text-zinc-400" />
            </button>
        </div>

        {{ $slot }}
    </nav>
</aside>
