<div x-data="{ toasts: [] }"
    x-on:toast.window="
    console.log('Toast event:', $event.detail);
    toasts.push({ id: Date.now(), message: $event.detail.message, type: $event.detail.type || 'success' });
    setTimeout(() => toasts.shift(), 3000);
"
    class="fixed top-4 left-1/2 -translate-x-1/2 z-[100] space-y-2 flex flex-col items-center">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-4"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="px-4 py-3 rounded-lg shadow-lg text-sm font-medium flex items-center gap-2 min-w-[220px] justify-center"
            :class="toast.type === 'success' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'">
            <i :class="toast.type === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation'"></i>
            <span x-text="toast.message"></span>
        </div>
    </template>
</div>
