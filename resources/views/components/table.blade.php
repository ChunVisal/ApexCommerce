@props(['headers' => 'headers', 'rows', 'as' => 'row'])

<table class="w-full text-sm">
    <thead>
        <tr class="text-left text-xs text-gray-500 dark:text-zinc-400 border-b border-gray-200 dark:border-zinc-800">
            <template x-for="(h, i) in {{ $headers }}" :key="i">
                <th class="pb-2 font-medium"
                    :class="{
                        'text-left': i === 0,
                        'text-right': i === {{ $headers }}.length - 1,
                        'text-center': i > 0 && i < {{ $headers }}.length - 1
                    }"
                    x-text="h"></th>
            </template>
        </tr>
    </thead>

    <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/50">
        <template x-for="{{ $as }} in {{ $rows }}" :key="{{ $as }}.id">
            <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition">
                {{ $slot }}
            </tr>
        </template>
    </tbody>
</table>
