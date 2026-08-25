{{-- resources/views/components/toast.blade.php --}}
<div x-data="{ toasts: [] }"
    x-on:toast.window="
        const toast = {
            id: Date.now(),
            message: $event.detail.message,
            title: $event.detail.title || ($event.detail.type === 'error' ? 'Something went wrong' : 'Success'),
            type: $event.detail.type || 'success'
        };
        toasts.push(toast);
        setTimeout(() => { toasts = toasts.filter(t => t.id !== toast.id); }, 3500);
    "
    class="fixed top-4 left-1/2 -translate-x-1/2 z-[100] flex flex-col items-center w-full max-w-sm px-4 space-y-2">
    <template x-for="toast in toasts" :key="toast.id">
        <div class="toast-animate w-full bg-white dark:bg-zinc-800 border rounded-lg shadow-xl overflow-hidden"
            :class="toast.type === 'success' ? 'border-emerald-200 dark:border-emerald-900' :
                'border-rose-200 dark:border-rose-900'">

            <div class="flex items-start gap-3 p-3.5">
                {{-- Status Icon --}}
                <div class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                    :class="toast.type === 'success' ? 'bg-emerald-100 dark:bg-emerald-900/40' :
                        'bg-rose-100 dark:bg-rose-900/40'">
                    <i class="text-sm"
                        :class="toast.type === 'success' ? 'fa-solid fa-check text-emerald-600 dark:text-emerald-400' :
                            'fa-solid fa-xmark text-rose-600 dark:text-rose-400'"></i>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold text-gray-900 dark:text-zinc-100" x-text="toast.title"></p>
                    <p class="text-xs text-gray-500 dark:text-zinc-400 mt-0.5" x-text="toast.message"></p>
                </div>

                {{-- Status Badge --}}
                <span class="shrink-0 text-[10px] font-bold uppercase px-2 py-0.5 rounded"
                    :class="toast.type === 'success' ?
                        'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400' :
                        'bg-rose-100 dark:bg-rose-900/40 text-rose-700 dark:text-rose-400'"
                    x-text="toast.type"></span>
            </div>

            {{-- Progress bar showing time until auto-dismiss --}}
            <div class="h-0.5 bg-gray-100 dark:bg-zinc-800">
                <div class="h-full" :class="toast.type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'"
                    style="width: 100%" x-init="$el.style.transition = 'width 3.5s linear';
                    requestAnimationFrame(() => $el.style.width = '0%')"></div>
            </div>
        </div>
    </template>
</div>

<style>
    @keyframes toast-in {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .toast-animate {
        animation: toast-in 0.3s ease-out;
    }
</style>
