@extends('layouts.app')

@section('content')
    @include('admin.settings.scripts')
    <div class="w-full p-5 bg-gray-100/80 dark:bg-black transition-colors duration-300" x-data="settingsPage()">
        <!-- Header -->
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800 dark:text-zinc-100">Settings</h1>
                <p class="text-xs text-gray-500 dark:text-zinc-400">Manage store identity, contact information, and default
                    tax rates.</p>
            </div>
        </div>

        <!-- Main Container -->
        <div
            class="bg-white dark:bg-zinc-900 rounded-md shadow-sm border border-gray-200 dark:border-zinc-800/80 overflow-hidden">

            <!-- Tab Navigation Header (2 Tabs Only) -->
            <div class="flex border-b border-gray-200 dark:border-zinc-800 bg-gray-50/50 dark:bg-zinc-900/50">
                <button type="button" @click="activeTab = 'general'"
                    :class="activeTab === 'general' ?
                        'border-[#0F6E8C] text-[#0F6E8C] bg-white dark:bg-zinc-900 font-semibold' :
                        'border-transparent text-gray-500 dark:text-zinc-400 hover:text-gray-700 dark:hover:text-zinc-300'"
                    class="flex items-center gap-2 px-5 py-3 text-xs border-b-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    General Settings
                </button>

                <button type="button" @click="activeTab = 'tax'"
                    :class="activeTab === 'tax' ?
                        'border-[#0F6E8C] text-[#0F6E8C] bg-white dark:bg-zinc-900 font-semibold' :
                        'border-transparent text-gray-500 dark:text-zinc-400 hover:text-gray-700 dark:hover:text-zinc-300'"
                    class="flex items-center gap-2 px-5 py-3 text-xs border-b-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                    </svg>
                    Tax & Checkout
                </button>
            </div>

            <!-- Form Body -->
            <form @submit.prevent="submitSettings($event)" enctype="multipart/form-data">
                @csrf

                <div class="p-6 space-y-4">

                    <!-- TAB 1: General Settings (Logo, Name, Address, Phone) -->
                    <div x-show="activeTab === 'general'" class="space-y-4">
                        <!-- Shop Logo -->
                        <!-- Company Logo Upload -->
                        <div class="w-full max-w-xs">
                            <label class="block text-xs font-semibold text-gray-800 dark:text-zinc-300 mb-1.5">
                                Company Logo
                            </label>

                            <div class="flex flex-col gap-2.5">
                                <!-- Preview Container (Full Width of Form Column, Fixed Square Ratio) -->
                                <div
                                    class="w-full h-20 rounded-xl border border-dashed border-gray-300 dark:border-zinc-700/80 bg-gray-50/80 dark:bg-zinc-900/60 flex items-center justify-center overflow-hidden transition hover:border-gray-400 dark:hover:border-zinc-600">
                                    <template x-if="logoPreview">
                                        <img :src="logoPreview" class="w-full h-20 object-contain p-3">
                                    </template>
                                    <template x-if="!logoPreview">
                                        <div
                                            class="flex flex-col items-center justify-center text-gray-500 dark:text-zinc-400 gap-2">
                                            <svg class="w-8 h-8 text-gray-400 dark:text-zinc-500" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <span class="text-[11px] font-semibold text-gray-500 dark:text-zinc-400">No logo
                                                uploaded</span>
                                        </div>
                                    </template>
                                </div>

                                <!-- File Input Button -->
                                <input type="file" name="logo" accept="image/*"
                                    @change="const file = $event.target.files[0]; if(file) { logoPreview = URL.createObjectURL(file) }"
                                    class="w-full text-xs font-medium text-gray-700 dark:text-zinc-300 border border-gray-300 dark:border-zinc-800 rounded-lg px-2.5 py-1.5 bg-white dark:bg-zinc-900 file:mr-2.5 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-[11px] file:font-semibold file:bg-gray-100 dark:file:bg-zinc-800 file:text-gray-800 dark:file:text-zinc-200 hover:file:bg-gray-200 dark:hover:file:bg-zinc-700 transition cursor-pointer">
                            </div>
                        </div>

                        <!-- Shop Name -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1">Shop Name</label>
                            <input type="text" name="shop_name" value="{{ App\Models\Setting::get('shop_name') }}"
                                placeholder="e.g. Apex Store"
                                class="w-full text-xs bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-100 border border-gray-300 dark:border-zinc-800 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
                        </div>

                        <!-- Address & Phone Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1">Address</label>
                                <input type="text" name="shop_address"
                                    value="{{ App\Models\Setting::get('shop_address') }}"
                                    placeholder="Street, City, Country"
                                    class="w-full text-xs bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-100 border border-gray-300 dark:border-zinc-800 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1">Phone
                                    Number</label>
                                <input type="text" name="shop_phone" value="{{ App\Models\Setting::get('shop_phone') }}"
                                    placeholder="+855 12 345 678"
                                    class="w-full text-xs bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-100 border border-gray-300 dark:border-zinc-800 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: Tax & Checkout -->
                    <div x-show="activeTab === 'tax'" class="space-y-4" style="display: none;">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1">Tax Rate
                                (%)</label>
                            <div class="relative max-w-[160px]">
                                <input type="number" step="0.01" name="tax_rate"
                                    value="{{ App\Models\Setting::get('tax_rate', '10') }}"
                                    class="w-full text-xs bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-100 border border-gray-300 dark:border-zinc-800 rounded-md pl-3 pr-7 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
                                <span
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-400 dark:text-zinc-500 pointer-events-none">%</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-zinc-400 mb-1">VIP Discount
                                (%)</label>
                            <div class="relative max-w-[160px]">
                                <input type="number" step="0.01" name="vip_discount"
                                    value="{{ App\Models\Setting::get('vip_discount', '5') }}"
                                    class="w-full text-xs bg-white dark:bg-zinc-900 text-gray-800 dark:text-zinc-100 border border-gray-300 dark:border-zinc-800 rounded-md pl-3 pr-7 py-2 focus:outline-none focus:ring-1 focus:ring-[#0F6E8C]">
                                <span
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold text-gray-400 dark:text-zinc-500 pointer-events-none">%</span>
                            </div>
                        </div>
                    </div>

                </div>


                <!-- Submit Button Footer -->
                <div
                    class="px-6 py-3 bg-gray-50/60 dark:bg-zinc-900/60 border-t border-gray-200 dark:border-zinc-800 flex justify-end">
                    <button type="submit" :disabled="submitting"
                        class="px-4 py-2 text-xs font-semibold text-white bg-[#0F6E8C] hover:bg-[#0c5972] rounded-md transition-colors flex items-center gap-2">
                        <span>
                            <template x-if="submitting">
                                <i class="fa-solid fa-spinner fa-spin"></i>
                            </template>
                            Save Settings
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
