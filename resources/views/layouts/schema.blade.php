<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: false, activeTab: '{{ array_key_first(is_array($schema) ? $schema : $schema->toArray()) }}', search: '' }" :class="{ 'dark': darkMode }">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Schema Viewer</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            500: '#16a34a',
                            600: '#15803d',
                            700: '#166534'
                        }
                    }
                }
            }
        }
    </script>
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body
    class="bg-gray-100 dark:bg-zinc-950 text-gray-900 dark:text-zinc-100 antialiased min-h-screen transition-colors duration-200">

    <div class="w-full max-w-none px-4 sm:px-6 lg:px-8 py-6">

        <!-- Header Bar -->
        <div
            class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 mb-5 border-b border-gray-300 dark:border-zinc-800">
            <div>
                <h1
                    class="text-2xl font-bold tracking-tight text-gray-900 dark:text-zinc-100 flex items-center gap-2.5">
                    <i class="fa-solid fa-database text-brand-600"></i>
                    <span>Database Schema</span>
                </h1>
                <p class="text-xs font-semibold text-gray-700 dark:text-zinc-400 mt-1">
                    Explore tables, columns, constraints, and data types
                </p>
            </div>

            <div class="flex items-center gap-3">
                <!-- Search Input -->
                <div class="relative flex-1 md:w-72">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-600 dark:text-zinc-500"></i>
                    <input type="text" x-model="search" placeholder="Filter tables..."
                        class="w-full pl-9 pr-3 py-1.5 text-xs font-semibold rounded-lg border border-gray-400 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-gray-900 dark:text-zinc-200 placeholder-gray-500 dark:placeholder-zinc-500 focus:outline-none focus:ring-2 focus:ring-brand-600">
                </div>

                <!-- Dark Mode Toggle Button -->
                <button @click="darkMode = !darkMode"
                    class="p-2 border border-gray-300 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-300 hover:bg-gray-200 dark:hover:bg-zinc-800 rounded-lg transition-colors">
                    <i class="fa-solid" :class="darkMode ? 'fa-sun text-amber-400' : 'fa-moon text-gray-800'"></i>
                </button>
            </div>
        </div>

        <!-- Top Horizontal Navigation Tabs -->
        <div
            class="bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-800/80 rounded-xl p-2 shadow-sm mb-6">
            <div class="flex items-center tab-container overflow-x-auto gap-2 scrollbar-thin">
                @foreach ($schema as $tableName => $columns)
                    <button x-show="'{{ $tableName }}'.toLowerCase().includes(search.toLowerCase())"
                        @click="activeTab = '{{ $tableName }}'"
                        :class="activeTab === '{{ $tableName }}'
                            ?
                            'bg-brand-600 text-white font-bold shadow-sm' :
                            'text-gray-800 dark:text-zinc-300 font-semibold hover:bg-gray-200 dark:hover:bg-zinc-800 border-gray-300 dark:border-zinc-800'"
                        class="flex items-center gap-2 px-3.5 py-2 text-xs rounded-lg border transition-all whitespace-nowrap shrink-0">
                        <i class="fa-solid fa-table-cells text-xs"></i>
                        <span>{{ $tableName }}</span>
                        <span class="px-1.5 py-0.5 text-[10px] rounded-md font-mono"
                            :class="activeTab === '{{ $tableName }}' ? 'bg-white/20 text-white' :
                                'bg-gray-200 dark:bg-zinc-800 text-gray-900 dark:text-zinc-300'">
                            {{ count($columns) }}
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Full Width Active Table View Panel -->
        <div class="w-full">
            @foreach ($schema as $tableName => $columns)
                <div x-show="activeTab === '{{ $tableName }}'" x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    class="bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-800/80 rounded-xl shadow-sm overflow-hidden">

                    <!-- Table Header -->
                    <div
                        class="px-6 py-4 border-b border-gray-300 dark:border-zinc-800/80 bg-gray-100 dark:bg-zinc-900/50 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-8 h-8 rounded-lg bg-brand-600 text-white flex items-center justify-center shadow-sm">
                                <i class="fa-solid fa-table text-sm"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900 dark:text-zinc-100 font-mono">
                                    {{ $tableName }}
                                </h2>
                                <p class="text-xs font-semibold text-gray-700 dark:text-zinc-400">
                                    Total {{ count($columns) }} attribute columns defined
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Full Width Data Table -->
                    <div class="overflow-x-auto tab-container w-full">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr
                                    class="border-b border-gray-300 dark:border-zinc-800 text-gray-900 dark:text-zinc-300 bg-gray-200/80 dark:bg-zinc-950/40">
                                    <th class="py-3 px-5 font-bold w-12 text-center">#</th>
                                    <th class="py-3 px-5 font-bold">Column Name</th>
                                    <th class="py-3 px-5 font-bold">Data Type</th>
                                    <th class="py-3 px-5 font-bold">Category</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-zinc-800 font-mono">
                                @php $i = 1; @endphp
                                @foreach ($columns as $column => $type)
                                    <tr class="hover:bg-gray-100 dark:hover:bg-zinc-800/40 transition-colors">
                                        <td
                                            class="py-3.5 px-5 text-center text-gray-700 dark:text-zinc-500 font-bold font-sans">
                                            {{ $i++ }}
                                        </td>
                                        <td class="py-3.5 px-5 font-bold text-gray-900 dark:text-zinc-100">
                                            <div class="inline-flex items-center gap-2">
                                                <span>{{ $column }}</span>

                                                @if ($column == 'id')
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-extrabold font-sans bg-rose-100 text-rose-800 dark:bg-rose-500/20 dark:text-rose-400 border border-rose-300 dark:border-rose-500/30">
                                                        <i class="fa-solid fa-key text-[9px]"></i> PK
                                                    </span>
                                                @endif

                                                @if (str_contains($column, '_id'))
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-extrabold font-sans bg-sky-100 text-sky-800 dark:bg-sky-500/20 dark:text-sky-400 border border-sky-300 dark:border-sky-500/30">
                                                        <i class="fa-solid fa-link text-[9px]"></i> FK
                                                    </span>
                                                @endif

                                                @if (in_array($column, ['created_at', 'updated_at', 'deleted_at']))
                                                    <span
                                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-extrabold font-sans bg-emerald-100 text-emerald-800 dark:bg-emerald-500/20 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-500/30">
                                                        <i class="fa-solid fa-clock text-[9px]"></i> Timestamp
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-5">
                                            <span
                                                class="inline-block px-2.5 py-1 rounded-md bg-gray-200 dark:bg-zinc-800 text-gray-900 dark:text-zinc-200 border border-gray-300 dark:border-zinc-700 text-[11px] font-bold">
                                                {{ $type }}
                                            </span>
                                        </td>
                                        <td
                                            class="py-3.5 px-5 font-sans font-semibold text-gray-800 dark:text-zinc-300">
                                            @if (str_contains($type, 'int'))
                                                <span class="inline-flex items-center gap-1.5"><i
                                                        class="fa-solid fa-hashtag text-indigo-600 dark:text-indigo-400"></i>
                                                    Integer</span>
                                            @elseif(str_contains($type, 'varchar') || str_contains($type, 'text'))
                                                <span class="inline-flex items-center gap-1.5"><i
                                                        class="fa-solid fa-font text-amber-600 dark:text-amber-400"></i>
                                                    String</span>
                                            @elseif(str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double'))
                                                <span class="inline-flex items-center gap-1.5"><i
                                                        class="fa-solid fa-coins text-emerald-600 dark:text-emerald-400"></i>
                                                    Decimal</span>
                                            @elseif(str_contains($type, 'date') || str_contains($type, 'time') || str_contains($type, 'timestamp'))
                                                <span class="inline-flex items-center gap-1.5"><i
                                                        class="fa-solid fa-calendar-days text-sky-600 dark:text-sky-400"></i>
                                                    Date / Time</span>
                                            @elseif(str_contains($type, 'json'))
                                                <span class="inline-flex items-center gap-1.5"><i
                                                        class="fa-solid fa-code text-purple-600 dark:text-purple-400"></i>
                                                    JSON</span>
                                            @elseif(str_contains($type, 'bool') || str_contains($type, 'tinyint(1)'))
                                                <span class="inline-flex items-center gap-1.5"><i
                                                        class="fa-solid fa-toggle-on text-rose-600 dark:text-rose-400"></i>
                                                    Boolean</span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5"><i
                                                        class="fa-solid fa-box text-gray-500"></i> Other</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

    </div>

</body>

</html>
