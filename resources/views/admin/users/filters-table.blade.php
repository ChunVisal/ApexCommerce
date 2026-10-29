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

    <x-table headers="userHeaders" rows="filteredUsers" as="user">
        {{-- User Info & Avatar --}}
        <td class="py-3">
            <button class="flex items-center gap-3 cursor-pointer text-left" @click="openDetail(user.id)">
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

        <td class="">
            <p class="text-xs text-gray-400 dark:text-zinc-400" x-text="user.phone"></p>
        </td>

        {{-- Role --}}
        <td class="">
            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full capitalize"
                :class="user.role === 'admin' ?
                    'bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400' :
                    'bg-[#0F6E8C]/10 dark:bg-[#0F6E8C]/30 text-[#0F6E8C] dark:text-[#4a9eb8]'"
                x-text="user.role">
            </span>
        </td>

        {{-- Account Status --}}
        <td class="">
            <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full"
                :class="user.status === 'active' ?
                    'bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400' :
                    'bg-gray-100 dark:bg-zinc-800 text-gray-500 dark:text-zinc-400'"
                x-text="user.status === 'active' ? 'Active' : 'Inactive'">
            </span>
        </td>

        {{-- Last Login --}}
        <td class=" text-xs text-gray-500 dark:text-zinc-400"
            x-text="user.last_login ? new Date(user.last_login).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric', hour:'2-digit', minute:'2-digit'}) : 'Never'">
        </td>
        {{-- Online / Offline Status Badge --}}
        <td class="">
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
        <td class="">
            <div class="flex items-center justify-end gap-2">
                <button @click="openEdit(user)" type="button"
                    class="text-gray-400 dark:text-zinc-500 hover:text-[#0F6E8C] transition-colors" title="Edit">
                    <x-heroicon-m-pencil-square class="w-5 h-5" />
                </button>

                <button @click="deleteUser(user.id, $el)" type="button"
                    class="text-red-500 hover:text-red-600 transition-colors" title="Delete">
                    <i class="mt-[4px] fa-solid fa-trash text-[15px]"></i>
                </button>
                {{-- <input type="checkbox" class="bulk-checkbox rounded border-gray-300 dark:border-zinc-600 cursor-pointer"
                    :data-id="user.id" @change="updateBulkBar()"> --}}
            </div>
        </td>
    </x-table>

    {{-- Empty state for filter/searching --}}
    <x-empty-state icon="heroicon-o-user-group" x-show="users.length > 0 && filteredUsers.length === 0"
        title="No users found" description="Try adjusting your filters or search." />

    {{-- Empty state, no users yet --}}
    <x-empty-state icon="heroicon-o-user-plus" x-show="users.length === 0" title="No users exist yet"
        description="Get started by adding your first user." cta="Add Your First User" action="openAdd()" />
</div>
