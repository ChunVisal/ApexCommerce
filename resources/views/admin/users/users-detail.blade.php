{{-- Profile Modal - Desktop PC Design --}}
<div x-show="showProfile" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/10 backdrop-blur-sm" style="display: none;">
    {{-- Overlay --}}
    <div x-show="showProfile" @click="closePanel()" class="absolute inset-0 bg-black/50"></div>

    {{-- Modal Content --}}
    <div x-show="showProfile" @click.outside="closePanel()"
        class="relative w-full max-w-4xl bg-white dark:bg-zinc-900 rounded-lg shadow-xl border border-gray-200 dark:border-zinc-800 max-h-[90vh] overflow-y-auto">

        {{-- Header --}}
        <div class="px-6 py-5 border-b border-gray-200 dark:border-zinc-800 flex items-center gap-4">
            <div class="relative">
                <div class="w-[65px] h-[65px] rounded-full flex items-center justify-center text-xs font-semibold text-white shrink-0 overflow-hidden"
                    :style="'background-color: ' + (form.role === 'admin' ? '#8B5CF6' : '#0F6E8C')">
                    <img x-show="form.avatar" :src="form.avatar" class="w-full h-full rounded-full object-cover">
                    <span x-show="!form.avatar" x-text="form.name ? form.name.charAt(0).toUpperCase() : '?'"></span>
                </div>
                <div x-show="form.is_online"
                    class="w-4 h-4 bg-green-500 rounded-full absolute top-0 right-0 border-2 border-white dark:border-zinc-800">
                </div>
            </div>
            <div class="flex-1">
                <h2 class="text-lg font-bold text-gray-800 dark:text-zinc-100" x-text="form.name"></h2>
                <p class="text-sm text-gray-500 dark:text-zinc-400"
                    x-text="form.role?.toUpperCase() + ' • ' + (form.employee_id || 'No ID')"></p>
                {{-- Created At --}}
                <p class="text-xs text-gray-400 dark:text-zinc-400 mt-0.5">
                    Created: <span
                        x-text="form.created_at ? new Date(form.created_at).toLocaleDateString('en-US', {month:'short', day:'numeric', year:'numeric'}) : '-'"></span>
                </p>
            </div>
            <span class="px-3 py-1 text-xs font-medium rounded-full"
                :class="form.is_online ? 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-400' :
                    'bg-gray-100 text-gray-500 dark:bg-zinc-700 dark:text-zinc-400'">
                <span x-text="form.is_online ? 'Online' : 'Offline'"></span>
            </span>
        </div>

        {{-- Body - 2 Columns --}}
        <div class="p-6 grid grid-cols-2 gap-5">
            {{-- Left Column --}}
            <div class="space-y-4" x-show="form.role === 'cashier'">

                <h4 class="text-xs font-semibold text-gray-400 dark:text-zinc-400 uppercase tracking-wider mb-2">
                    Employee Info</h4>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-zinc-400">Shift</span>
                        <span class="font-medium text-gray-800 dark:text-zinc-200 capitalize"
                            x-text="form.shift?.replace(/-/g, ' → ') || '-'"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-zinc-400">Hire Date</span>
                        <span class="font-medium text-gray-800 dark:text-zinc-200"
                            x-text="form.hire_date || '-'"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-zinc-400">Salary</span>
                        <span class="font-medium text-[#0F6E8C]">$<span x-text="form.salary || '0'"></span></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-zinc-400">Status</span>
                        <span class="font-medium"
                            :class="form.status === 'active' ? 'text-green-600' : 'text-red-600'"
                            x-text="form.status"></span>
                    </div>
                </div>
            </div>


            {{-- Right Column --}}
            <div class="space-y-4">
                <div>
                    <h4 class="text-xs font-semibold text-gray-400 dark:text-zinc-400 uppercase tracking-wider mb-2">
                        Contact</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-zinc-400">Email</span>
                            <span class="font-medium text-gray-800 dark:text-zinc-200" x-text="form.email"></span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-zinc-400">Phone</span>
                            <span class="font-medium text-gray-800 dark:text-zinc-200"
                                x-text="form.phone || '-'"></span>
                        </div>
                    </div>
                </div>

                {{-- Address Card --}}
                <div>
                    <h4 class="text-xs font-semibold text-gray-400 dark:text-zinc-400 uppercase tracking-wider mb-2">
                        Address</h4>
                    <div class="bg-gray-50 dark:bg-zinc-800 rounded-md p-3">
                        <p class="text-sm text-gray-800 dark:text-zinc-200 whitespace-pre-wrap"
                            x-text="form.address || 'No address provided'"></p>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-semibold text-gray-400 dark:text-zinc-400 uppercase tracking-wider mb-2">
                        Security</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm" x-show="form.role === 'cashier'">
                            <span class="text-gray-500 dark:text-zinc-400">Quick PIN</span>
                            <span class="font-medium text-gray-800 dark:text-zinc-200">••••</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-zinc-400">Last Login</span>
                            <span class="font-medium text-gray-800 dark:text-zinc-200"
                                x-text="form.last_login ? new Date(form.last_login).toLocaleString('en-US', {month:'short', day:'numeric', year:'numeric', hour:'2-digit', minute:'2-digit'}) : 'Never'"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div
            class="px-6 py-4 border-t border-gray-200 dark:border-zinc-800 flex items-center justify-end gap-2 bg-gray-50 dark:bg-zinc-800/30 rounded-b-lg">

            <button @click="closePanel(); setTimeout(() => { viewMode = 'edit'; openEdit(form); open = true; }, 100)"
                class="px-4 py-2 text-xs font-semibold text-white bg-[#0F6E8C] rounded-md hover:bg-[#0c5972] transition">
                <i class="fa-solid fa-pen mr-1"></i> Edit User
            </button>
            <button @click="closePanel()"
                class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-zinc-300 border border-gray-300 dark:border-zinc-600 rounded-md hover:bg-white dark:hover:bg-zinc-700 transition">
                Close
            </button>
        </div>
    </div>
</div>
