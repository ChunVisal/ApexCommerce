@props(['headers', 'rows', 'as' => 'row'])

<div class="tab-container max-h-[600px] overflow-y-auto overflow-x-auto">
    <table class="w-full text-sm whitespace-nowrap">
        <thead>
            <tr
                class="text-left text-xs text-gray-700 dark:text-zinc-400 border-b border-gray-200 dark:border-zinc-800 [&>th:last-child]:pr-1.5">
                <template x-for="(h, i) in {{ $headers }}" :key="i">
                    <th class="pb-2 font-medium px-4"
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
            <template x-for="({{ $as }}, index) in {{ $rows }}" :key="{{ $as }}.id ?? index">
                <tr
                    class="hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition [&>td]:text-center 
                    [&>td:first-child]:text-left [&>td:last-child]:text-right [&>td:last-child]:pr-1.5 [&>td]:px-4 [&>td]:py-3">
                    {{ $slot }}
                </tr>
            </template>
        </tbody>
    </table>
</div>
