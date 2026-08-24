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
        <div x-transition:enter="transition-transform transition-opacity duration-500"
            x-transition:enter-start="opacity-0 -translate-y-10 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition-transform transition-opacity duration-300"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-8 scale-90" x-show="true"
            class="rounded-sm  w-full shadow-xl border-2 overflow-hidden"
            :class="{
                // Success
                'bg-emerald-500 border-emerald-500 text-white dark:bg-emerald-600 dark:border-emerald-400': toast.type==='success',
                // Error
                'bg-rose-500 border-rose-700 text-white dark:bg-rose-600 dark:border-rose-400': toast.type==='error'
            }">
            <div class="flex items-start gap-3 p-4">
                {{-- Status Icon --}}
                <div class="shrink-0 w-8 h-8 flex items-center justify-center"
                    :class="toast.type === 'success' ?
                        'bg-emerald-700 dark:bg-emerald-400' :
                        'bg-rose-700 dark:bg-rose-400'"
                    style="border-radius:0">
                    <i class="text-base"
                        :class="toast.type === 'success' ?
                            'fa-solid fa-check text-white' :
                            'fa-solid fa-xmark text-white dark:text-rose-900'"></i>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold"
                        :class="toast.type === 'success' ?
                            'text-white dark:text-emerald-50' :
                            'text-white dark:text-rose-50'"
                        x-text="toast.title">
                    </p>
                    <p class="text-xs mt-1"
                        :class="toast.type === 'success' ?
                            'text-white' :
                            'text-rose-100 dark:text-rose-200'"
                        x-text="toast.message">
                    </p>
                </div>

                {{-- Status Badge --}}
                <span class="shrink-0 text-[10px] font-bold uppercase px-2 py-0.5"
                    :class="toast.type === 'success' ?
                        'bg-emerald-700 text-emerald-100 dark:bg-emerald-200 dark:text-emerald-900' :
                        'bg-rose-700 text-rose-100 dark:bg-rose-200 dark:text-rose-900'"
                    style="border-radius:0" x-text="toast.type"></span>
            </div>

            {{-- Progress bar showing time until auto-dismiss --}}
            <div class="h-1"
                :class="toast.type === 'success' ?
                    'bg-emerald-700 dark:bg-emerald-400' :
                    'bg-rose-700 dark:bg-rose-400'"
                style="border-radius:0">
                <div class="h-full"
                    :class="toast.type === 'success' ?
                        'bg-emerald-300 dark:bg-emerald-200' :
                        'bg-rose-100 dark:bg-rose-200'"
                    style="width: 100%; border-radius:0" x-init="$el.style.transition = 'width 3.5s linear';
                    requestAnimationFrame(() => $el.style.width = '0%')"></div>
            </div>
        </div>
    </template>
</div>
