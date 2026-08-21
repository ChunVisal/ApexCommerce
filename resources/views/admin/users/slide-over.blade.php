<x-slide-over>
    {{-- Header --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-zinc-800">
        <h2 class="text-base font-semibold text-gray-800 dark:text-zinc-100" x-text="editMode ? 'Edit User' : 'Add User'">
        </h2>
        <button @click="open = false"
            class="text-gray-400 dark:text-zinc-500 hover:text-gray-600 dark:hover:text-zinc-300">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>
    </div>

    <form @submit.prevent="submitForm()" class="flex-1 flex flex-col overflow-y-auto px-5 py-4 space-y-4">
        {{-- Role Selector --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-2">Role</label>
            <div class="grid grid-cols-2 gap-2">
                <button type="button" @click="form.role = 'admin'"
                    class="flex items-center justify-center gap-2 py-2.5 rounded-md text-xs font-semibold border transition w-full"
                    :class="form.role === 'admin' ?
                        'bg-purple-50 dark:bg-purple-950/40 border-purple-300 dark:border-purple-800 text-purple-700 dark:text-purple-400' :
                        'border-gray-300 dark:border-zinc-700 text-gray-500 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800'">
                    <i class="fa-solid fa-user-shield"></i> Admin
                </button>
                <button type="button" @click="form.role = 'cashier'"
                    class="flex items-center justify-center gap-2 py-2.5 rounded-md text-xs font-semibold border transition w-full"
                    :class="form.role === 'cashier' ?
                        'bg-blue-50 dark:bg-blue-950/40 border-blue-300 dark:border-blue-800 text-blue-700 dark:text-blue-400' :
                        'border-gray-300 dark:border-zinc-700 text-gray-500 dark:text-zinc-400 hover:bg-gray-50 dark:hover:bg-zinc-800'">
                    <i class="fa-solid fa-cash-register"></i> Cashier
                </button>
            </div>
        </div>

        {{-- Basic Info --}}
        <div class="space-y-3">
            <p class="text-xs font-semibold text-gray-400 dark:text-zinc-500 uppercase">Basic Info</p>
            <div>
                <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-1">Full Name *</label>
                <input type="text" x-model="form.name" required placeholder="e.g. Sokha Chan"
                    class="w-full text-sm bg-transparent border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] text-gray-800 dark:text-zinc-200 placeholder-gray-400">
            </div>
            <div>
                <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-1">Email Address *</label>
                <input type="email" x-model="form.email" required placeholder="name@bluetech.com"
                    class="w-full text-sm bg-transparent border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] text-gray-800 dark:text-zinc-200 placeholder-gray-400">
            </div>
            <div>
                <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-1">Phone Number</label>
                <input type="text" x-model="form.phone" placeholder="e.g. 012 345 678"
                    class="w-full text-sm bg-transparent border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] text-gray-800 dark:text-zinc-200 placeholder-gray-400">
            </div>
            <div>
                <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-1">Address</label>
                <textarea x-model="form.address" rows="2" placeholder="Enter address..."
                    class="w-full text-sm bg-transparent border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] text-gray-800 dark:text-zinc-200 placeholder-gray-400 resize-none"></textarea>
            </div>
            {{-- Avatar Upload --}}
            <div>
                <label
                    class="block text-[12px] font-bold tracking-wider uppercase text-gray-600 dark:text-zinc-400 mb-1">
                    Profile Photo
                </label>
                {{-- Preview --}}
                <div x-show="avatarPreview || form.avatar_url" class="mb-2 relative inline-block">
                    <img :src="avatarPreview || form.avatar_url"
                        class="h-24 w-24 object-cover rounded-md border border-gray-200 dark:border-zinc-700">
                    <button type="button" @click="avatarPreview = ''; form.avatar_url = ''; form.avatar_file = null;"
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs hover:bg-red-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                {{-- URL Input --}}
                <input type="url" x-model="form.avatar_url" @input="avatarPreview = ''; form.avatar_file = null;"
                    placeholder="Paste image URL..."
                    class="w-full text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-zinc-100 border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] mb-2">
                {{-- Divider --}}
                <div class="flex items-center gap-2 mb-2">
                    <div class="flex-1 h-px bg-gray-200 dark:bg-zinc-700"></div>
                    <span class="text-xs text-gray-400 dark:text-zinc-500">or upload file</span>
                    <div class="flex-1 h-px bg-gray-200 dark:bg-zinc-700"></div>
                </div>
                {{-- File Upload --}}
                <label
                    class="flex items-center justify-center gap-2 w-full px-3 py-2 border border-dashed border-gray-300 dark:border-zinc-600 rounded-md cursor-pointer hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                    <i class="fa-solid fa-arrow-up-from-bracket text-gray-400 text-sm"></i>
                    <span class="text-xs text-gray-500 dark:text-zinc-400"
                        x-text="form.avatar_file ? form.avatar_file.name : 'Click to upload photo'"></span>
                    <input type="file" accept="image/*" class="hidden" @change="handleAvatarFile($event)">
                </label>
            </div>
        </div>

        {{-- Security --}}
        <div class="space-y-3">
            <p class="text-xs font-semibold text-gray-400 dark:text-zinc-500 uppercase">Security</p>
            <div class="grid grid-cols-2 gap-3">
                <div x-data="{ showPassword: false }">
                    <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-1">
                        Password <span x-show="editMode" class="text-gray-400 font-normal">(keep blank)</span>
                    </label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" x-model="form.password" :required="!editMode"
                            placeholder="••••••••"
                            class="w-full text-sm bg-transparent border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] text-gray-800 dark:text-zinc-200 placeholder-gray-400">

                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 dark:text-zinc-500 text-gray-600 dark:hover:text-zinc-400">
                            <x-heroicon-o-eye x-show="!showPassword" class="h-5 w-5" />
                            <x-heroicon-o-eye-slash x-show="showPassword" class="h-5 w-5" />
                        </button>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-1">Confirm Password</label>
                    <input type="password" x-model="form.password_confirmation" :required="!editMode"
                        placeholder="••••••••"
                        class="w-full text-sm bg-transparent border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] text-gray-800 dark:text-zinc-200 placeholder-gray-400">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <label class="text-xs text-gray-500 dark:text-zinc-400">Status</label>
            <div class="flex gap-1">
                <button type="button" @click="form.status = 'active'" class="px-3 py-1 text-xs rounded-l-md transition"
                    :class="form.status === 'active' ?
                        'bg-green-500 text-white' :
                        'bg-gray-200 dark:bg-zinc-700 text-gray-500'">Active</button>
                <button type="button" @click="form.status = 'inactive'"
                    class="px-3 py-1 text-xs rounded-r-md transition"
                    :class="form.status === 'inactive' ?
                        'bg-red-500 text-white' :
                        'bg-gray-200 dark:bg-zinc-700 text-gray-500'">Inactive</button>
            </div>
        </div>

        {{-- CASHIER ONLY --}}
        <div x-show="form.role === 'cashier'" class="space-y-3">
            <p class="text-xs font-semibold text-blue-500 dark:text-blue-400 uppercase">Cashier Details</p>
            <div>
                <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-1">Employee ID</label>
                <input type="text" x-model="form.employee_id" placeholder="Auto-generated"
                    class="w-full text-sm bg-gray-50 dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 text-gray-800 dark:text-zinc-200"
                    readonly>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-1">Shift</label>
                    <select x-model="form.shift"
                        class="w-full text-sm bg-white dark:bg-zinc-900 border border-gray-300 dark:border-zinc-800 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] focus:border-[#0F6E8C] text-gray-800 dark:text-zinc-200">
                        <option value="" class="bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200">
                            Select</option>
                        <option value="morning-afternoon"
                            class="bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200">
                            Morning → Afternoon
                        </option>
                        <option value="afternoon-night"
                            class="bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200">
                            Afternoon → Night
                        </option>
                        <option value="night-morning"
                            class="bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200">
                            Night → Morning
                        </option>
                        <option value="full-morning"
                            class="bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200">
                            Full (Morning-Afternoon)
                        </option>
                        <option value="full-night" class="bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-200">
                            Full (Night-Morning)
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-1">Hire Date</label>
                    <div class="relative">
                        <input type="date" x-model="form.hire_date"
                            class="w-full text-sm bg-transparent border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] text-gray-800 dark:text-zinc-200 [color-scheme:light] dark:[color-scheme:dark]">
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-1">Salary ($)</label>
                    <input type="number" x-model="form.salary" step="0.01" placeholder="0.00"
                        class="w-full text-sm bg-transparent border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] text-gray-800 dark:text-zinc-200 placeholder-gray-400">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-zinc-400 mb-1">Quick Login PIN</label>
                    <input type="password" x-model="form.pin" maxlength="4" placeholder="4 digits"
                        class="w-full text-sm bg-transparent border border-gray-300 dark:border-zinc-700 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C] text-gray-800 dark:text-zinc-200 placeholder-gray-400">
                </div>
            </div>
        </div>

        {{-- ADMIN ONLY --}}
        <div x-show="form.role === 'admin'" class="space-y-3">
            <p class="text-xs font-semibold text-purple-500 dark:text-purple-400 uppercase">
                Admin Privileges</p>
            <div
                class="bg-purple-50 dark:bg-purple-950/20 border border-purple-200 dark:border-purple-800 rounded-md p-3">
                <p class="text-xs text-purple-700 dark:text-purple-400">
                    <i class="fa-solid fa-shield-halved mr-1"></i>
                    Admin has full access to all features: products, inventory, users,
                    reports, and settings.
                </p>
            </div>
        </div>
    </form>

    {{-- Footer --}}
    <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-200 dark:border-zinc-800 shrink-0">
        <button @click="open = false" type="button"
            class="px-4 py-2 text-xs font-semibold text-gray-600 dark:text-zinc-300 border border-gray-300 dark:border-zinc-700 rounded-md hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
            Cancel
        </button>
        <button @click="submitForm()" type="button" :disabled="submitting"
            class="px-4 py-2 text-xs font-semibold text-white bg-[#0F6E8C] rounded-md hover:bg-[#0c5972] transition disabled:opacity-50 disabled:cursor-not-allowed">
            <i x-show="submitting" class="fa-solid fa-spinner fa-spin mr-1"></i>
            <span x-text="editMode ? 'Save Changes' : 'Save User'"></span>
        </button>
    </div>
</x-slide-over>
