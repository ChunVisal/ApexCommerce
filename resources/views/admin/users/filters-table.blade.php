<!-- Filters -->
<div class="flex flex-wrap items-center gap-3 mb-4">
    <div class="relative flex-1 min-w-[200px]">
        <i
            class="fa-solid fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 text-xs"></i>
        <input type="text" x-model="searchQuery" placeholder="Search by name, email or role..."
            class="bg-white dark:bg-zinc-900 w-full pl-8 pr-3 py-1.5 text-xs bg-transparent border border-gray-300 dark:border-zinc-700 rounded-md focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] text-gray-800 dark:text-zinc-200 placeholder-gray-400 dark:placeholder-zinc-500">
        <button type="button" x-show="searchQuery" @click="searchQuery = ''; filterUsers()"
            class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500 z-10">
            ✕
        </button>
    </div>
    <div class="relative">
        <select x-model="roleFilter"
            class="bg-white dark:bg-zinc-900 appearance-none text-xs bg-transparent border border-gray-300 dark:border-zinc-700 rounded-md pl-3 pr-8 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] text-gray-800 dark:text-zinc-200">
            <option value="all" class="bg-white dark:bg-zinc-900">All Roles</option>
            <option value="admin" class="bg-white dark:bg-zinc-900">Admin</option>
            <option value="cashier" class="bg-white dark:bg-zinc-900">Cashier</option>
        </select>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
            stroke="currentColor"
            class="w-3.5 h-3.5 absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </div>
    <div class="relative">
        <select x-model="statusFilter"
            class="bg-white dark:bg-zinc-900 appearance-none text-xs bg-transparent border border-gray-300 dark:border-zinc-700 rounded-md pl-3 pr-8 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] text-gray-800 dark:text-zinc-200">
            <option value="all" class="bg-white dark:bg-zinc-900">All Status</option>
            <option value="active" class="bg-white dark:bg-zinc-900">Active</option>
            <option value="inactive" class="bg-white dark:bg-zinc-900">Inactive</option>
        </select>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
            stroke="currentColor"
            class="w-3.5 h-3.5 absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 dark:text-zinc-500 pointer-events-none">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </div>
    {{-- Bulk Action Bar --}}
    <div id="bulkBar" style="display:none;"
        class="flex items-center justify-between pl-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/60 rounded-md">
        <span class="text-xs text-red-600 dark:text-red-400 font-medium mr-4">
            <span id="bulkCount">0</span> selected
        </span>
        <div class="flex items-center ">
            <button onclick="bulkDeactivate()"
                class="px-3 py-[4.5px] text-[12px] font-medium text-white bg-amber-500 hover:bg-amber-600 transition">
                Deactivate
            </button>
            <button onclick="bulkDelete()"
                class="px-3 py-[4.5px] text-[12px] font-medium text-white bg-red-500 hover:bg-red-600 transition">
                Delete Selected
            </button>
            <button onclick="cancelBulkMode()"
                class="px-3 py-[4.5px] text-[12px] font-medium text-gray-600 dark:text-zinc-300 border border-gray-300 dark:border-zinc-600 rounded-r-md">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="bg-white dark:bg-zinc-900 p-4 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/60">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr
                    class="text-left text-xs text-gray-500 dark:text-zinc-400 border-b border-gray-200 dark:border-zinc-800">
                    <th class="pb-2 pr-4 font-medium">User</th>
                    <th class="pb-2 px-4 font-medium">Role</th>
                    <th class="pb-2 px-4 font-medium">Phone Number</th>
                    <th class="pb-2 px-4 font-medium text-center">Status</th>
                    <th class="pb-2 px-4 font-medium">Last Login</th>
                    <th class="pb-2 px-4 font-medium">Online</th>
                    <th class="pb-2 pl-4 font-medium text-right">Actions</th>
                    <th class="pb-2 px-2 font-medium">
                        <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes(this)"
                            class="rounded border-gray-300 dark:border-zinc-600 cursor-pointer">
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-zinc-800/50">
                <template x-for="user in filteredUsers" :key="user.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800/30 transition">

                        {{-- User Info & Avatar --}}
                        <td class="py-3 pr-4">
                            <button class="flex items-center gap-3 cursor-pointer text-left"
                                @click="openDetail(user.id)">
                                <div class="relative">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-xs font-semibold text-white shrink-0 overflow-hidden"
                                        :style="`background-color: ${user.role === 'admin' ? '#8B5CF6' : '#0F6E8C'};`">
                                        <template x-if="user.avatar">
                                            <img :src="user.avatar" class="w-full h-full object-cover" />
                                        </template>
                                        <template x-if="!user.avatar">
                                            <span x-text="getInitials(user.name)"></span>
                                        </template>
                                    </div>

                                    {{-- Online Badge Indicator --}}
                                    <template x-if="user.is_online">
                                        <div
                                            class="w-3.5 h-3.5 bg-green-500 rounded-full absolute top-0 right-0 border-2 border-white dark:border-zinc-800">
                                        </div>
                                    </template>
                                </div>

                                <div class="flex flex-col items-start">
                                    <p class="font-medium text-gray-800 dark:text-zinc-200" x-text="user.name"></p>
                                    <p class="text-xs text-gray-400 dark:text-zinc-500" x-text="user.email"></p>
                                </div>
                            </button>
                        </td>

                        <td class="py-3 px-4">
                            <p class="text-xs text-gray-400 dark:text-zinc-400" x-text="user.phone"></p>
                        </td>

                        {{-- Role --}}
                        <td class="py-3 px-2 text-center">
                            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full capitalize"
                                :class="user.role === 'admin' ?
                                    'bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' :
                                    'bg-[#0F6E8C]/10 dark:bg-[#0F6E8C]/30 text-[#0F6E8C] dark:text-[#4a9eb8]'"
                                x-text="user.role">
                            </span>
                        </td>

                        {{-- Account Status --}}
                        <td class="py-3 px-4 text-center">
                            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full"
                                :class="user.status === 'active' ?
                                    'bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400' :
                                    'bg-gray-100 dark:bg-zinc-800 text-gray-500 dark:text-zinc-400'"
                                x-text="user.status === 'active' ? 'Active' : 'Inactive'">
                            </span>
                        </td>

                        {{-- Last Login --}}
                        <td class="py-3 px-4 text-xs text-gray-500 dark:text-zinc-400"
                            x-text="user.last_login ? new Date(user.last_login).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric', hour:'2-digit', minute:'2-digit'}) : 'Never'">
                        </td>
                        {{-- Online / Offline Status Badge --}}
                        <td class="py-3 px-4">
                            <template x-if="user.is_online">
                                <span
                                    class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-400">Online</span>
                            </template>
                            <template x-if="!user.is_online">
                                <span
                                    class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-500 dark:bg-zinc-800 dark:text-zinc-400">Offline</span>
                            </template>
                        </td>

                        {{-- Actions --}}
                        <td class="py-3 pl-4">
                            <div class="flex items-center justify-end gap-3">
                                <button @click="openEdit(user)" type="button"
                                    class="text-gray-400 dark:text-zinc-500 hover:text-[#0F6E8C] transition-colors"
                                    title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button @click="deleteUser(user.id, $el)" type="button"
                                    class="text-red-500 hover:text-red-600 transition-colors" title="Delete">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </td>

                        {{-- Checkbox --}}
                        <td class="pl-2 pb-1">
                            <input type="checkbox"
                                class="bulk-checkbox rounded border-gray-300 dark:border-zinc-600 cursor-pointer"
                                :data-id="user.id" @change="updateBulkBar()">
                        </td>
                    </tr>
                </template>

                {{-- Empty State --}}
                <tr x-show="!filteredUsers || filteredUsers.length === 0">
                    <td colspan="7" class="text-center py-16">
                        <div class="flex flex-col items-center justify-center">
                            <div
                                class="w-16 h-16 mb-4 bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-400 dark:text-zinc-500" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-zinc-300 mb-1">No users found</h3>
                            <p class="text-xs text-gray-400 dark:text-zinc-500">Try adjusting your filters</p>
                        </div>
                    </td>
                </tr>

            </tbody>

            {{-- Empty state row - add this at the end of <tbody> --}}
            <tr id="noUsersRow" style="display: none;">
                <td colspan="6" class="text-center py-16">
                    <div class="flex flex-col items-center justify-center">
                        <div
                            class="w-16 h-16 mb-4 bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400 dark:text-zinc-500" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d=" M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0
        00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331
        0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0
        016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-zinc-300 mb-1">No users found
                        </h3>
                        <p class="text-xs text-gray-400 dark:text-zinc-500">Try adjusting your filters</p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
