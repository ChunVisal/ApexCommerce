<!-- Filters -->
<div class="flex flex-wrap items-center gap-3 mb-4">
    <x-search-input placeholder="Search by name, email or role..." />

    <x-filter-select model="roleFilter">
        <option value="all">All Roles</option>
        <option value="admin">Admin</option>
        <option value="cashier">Cashier</option>
    </x-filter-select>

    <x-filter-select model="statusFilter">
        <option value="all">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
    </x-filter-select>

    {{-- Bulk Action Bar --}}
    <div id="bulkBar" style="display:none;"
        class="flex items-center justify-between pl-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/60 rounded-md">
        <span class="text-xs text-red-600 dark:text-red-400 font-medium mr-4">
            <span id="bulkCount">0</span> selected
        </span>
        <div class="flex items-center ">
            <button @click="bulkDeactivate()"
                class="px-3 py-[4.5px] text-[12px] font-medium text-white bg-amber-500 hover:bg-amber-600 transition">
                Deactivate
            </button>
            <button @click="bulkDelete()"
                class="px-3 py-[4.5px] text-[12px] font-medium text-white bg-red-500 hover:bg-red-600 transition">
                Delete Selected
            </button>
            <button @click="cancelBulkMode()"
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
                    <th class="pb-2 px-4 font-medium">Phone Number</th>
                    <th class="pb-2 font-medium text-center">Role</th>
                    <th class="pb-2 px-4 font-medium text-center">Status</th>
                    <th class="pb-2 px-4 font-medium">Last Login</th>
                    <th class="pb-2 px-4 font-medium">Online</th>
                    <th class="pb-2 pl-4 font-medium text-right">Actions</th>
                    <th class="pb-2 px-2 font-medium">
                        <input type="checkbox" @change="toggleAllCheckboxes($el)"
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
                        <td class="py-3 text-center">
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
                            <div class="flex items-center justify-end gap-2">
                                <button @click="openEdit(user)" type="button"
                                    class="text-gray-400 dark:text-zinc-500 hover:text-[#0F6E8C] transition-colors"
                                    title="Edit">
                                    <x-heroicon-m-pencil-square class="w-5 h-5" />
                                </button>

                                <button @click="deleteUser(user.id, $el)" type="button"
                                    class="text-red-500 hover:text-red-600 transition-colors" title="Delete">
                                    <i class="mt-[4px] fa-solid fa-trash text-[15px]"></i>
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

                {{-- Empty State for filter/searching --}}
                <tr x-show="users.length > 0 && filteredUsers.length === 0">
                    <td colspan="7" class="text-center py-16">
                        <div class="flex flex-col items-center justify-center">
                            <div
                                class="w-16 h-16 mb-4 bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center">
                                <x-heroicon-o-user-group class="w-8 h-8 text-gray-400 dark:text-zinc-500" />
                            </div>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-zinc-300 mb-1">No users found</h3>
                            <p class="text-xs text-gray-400 dark:text-zinc-500">Try adjusting your filters or search.
                            </p>
                        </div>
                    </td>
                </tr>

                {{-- Empty State if no users exist at all --}}
                <tr x-show="users.length === 0">
                    <td colspan="7" class="text-center py-16">
                        <div class="flex flex-col items-center justify-center">
                            <div
                                class="w-16 h-16 mb-4 bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center">
                                <x-heroicon-o-user-plus class="w-8 h-8 text-gray-400 dark:text-zinc-500" />
                            </div>
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-zinc-300 mb-1">
                                No users exist yet
                            </h3>
                            <p class="text-xs text-gray-400 dark:text-zinc-500 mb-3">Get started by adding your first
                                user.</p>
                            <button
                                @click="openAddUser && typeof openAddUser === 'function' ? openAddUser() : openAdd()"
                                class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold text-white bg-[#0F6E8C] rounded-md hover:bg-[#0c5972] transition">
                                <x-heroicon-m-plus class="w-4 h-4" />
                                Add Your First User
                            </button>
                        </div>
                    </td>
                </tr>

        </table>
    </div>
</div>
