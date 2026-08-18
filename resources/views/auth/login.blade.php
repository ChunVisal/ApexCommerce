@extends('layouts.guest')

@section('content')
    <div class="h-screen w-screen flex items-center justify-center">

        <div class="w-full max-w-4xl grid grid-cols-1 sm:grid-cols-2 bg-white">

            <!-- LEFT -->
            <div class="p-10 flex flex-col justify-evenly">

                <!-- Logo -->
                <div class="flex items-center gap-3 mb-8">
                    <img src="{{ 'images/logo.png' }}" alt="Logo" class="w-25 h-20 object-cover"
                        onerror="this.style.display='none'">
                    <div>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 text-red-600 text-sm px-4 py-2 rounded mb-4">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#0F6E8C] text-sm"
                            placeholder="admin@blue.com" required autofocus>
                    </div>

                    <div class="mb-4" x-data="{ showPassword: false }">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">
                            Password
                        </label>

                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password"
                                class="w-full px-4 py-2 pr-10 border border-gray-300 rounded focus:outline-none focus:border-[#0F6E8C] text-sm"
                                placeholder="••••••••" required>

                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-600 hover:text-gray-700">
                                <x-heroicon-o-eye x-show="!showPassword" class="h-5 w-5" />
                                <x-heroicon-o-eye-slash x-show="showPassword" class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-sm mb-5">
                        <label class="flex items-center text-gray-500">
                            <input type="checkbox" name="remember" class="mr-2 border-2 border-gray-400"> Remember
                        </label>
                        <a href="{{ route('forget-password') }}" class="text-p text-sm">Forgot password?</a>
                    </div>

                    <div class="flex gap-2" x-data="{ open: false, pin: '' }" x-cloak>
                        <button @click="open = true" type="button"
                            class="px-6 py-2 bg-green-600 text-white text-sm font-semibold rounded hover:bg-green-500 transition">
                            Quick Login
                        </button>

                        <button type="submit" id="submitBtn"
                            class="flex-1 w-full bg-p hover:bg-[#0E5A93] text-white font-semibold py-2 rounded transition text-sm flex items-center justify-center">
                            <span id="btnText">Login</span>
                            <x-heroicon-o-arrow-path id="btnSpinner" class="animate-spin h-5 w-5 text-white hidden ml-2" />
                        </button>


                        <template x-teleport="body">
                            <div x-show="open" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title"
                                role="dialog" aria-modal="true">
                                <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                                    <div x-show="open" x-transition:enter="ease-out duration-300"
                                        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0" @click="open = false; pin = ''"
                                        class="fixed inset-0 bg-gray-500/75 transition-opacity"></div>

                                    <div x-show="open" x-transition:enter="ease-out duration-300"
                                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave="ease-in duration-200"
                                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                                        class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xs p-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-base font-semibold text-gray-800" id="modal-title">
                                                Cashier Quick Login
                                            </h3>
                                            <button @click="open = false; pin = ''" type="button"
                                                class="text-gray-400 hover:text-gray-600 transition">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>

                                        <form action="{{ route('cashier.pin-login') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="pin" :value="pin">
                                            <div class="flex justify-center gap-3 my-5">
                                                <template x-for="i in 4" :key="i">
                                                    <div class="w-10 h-10 rounded-full border-2 border-gray-300 flex items-center justify-center text-lg font-bold transition-all"
                                                        :class="pin.length >= i ? 'bg-[#0F6E8C] border-[#0F6E8C] text-white' :
                                                            'bg-gray-50'">
                                                        <span x-show="pin.length >= i">●</span>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="grid grid-cols-3 gap-2">
                                                <button type="button" @click="if(pin.length < 4) pin += '1'"
                                                    class="py-3 bg-gray-100 rounded-lg text-lg font-bold text-gray-800 hover:bg-gray-200 active:scale-95 transition">1</button>
                                                <button type="button" @click="if(pin.length < 4) pin += '2'"
                                                    class="py-3 bg-gray-100 rounded-lg text-lg font-bold text-gray-800 hover:bg-gray-200 active:scale-95 transition">2</button>
                                                <button type="button" @click="if(pin.length < 4) pin += '3'"
                                                    class="py-3 bg-gray-100 rounded-lg text-lg font-bold text-gray-800 hover:bg-gray-200 active:scale-95 transition">3</button>
                                                <button type="button" @click="if(pin.length < 4) pin += '4'"
                                                    class="py-3 bg-gray-100 rounded-lg text-lg font-bold text-gray-800 hover:bg-gray-200 active:scale-95 transition">4</button>
                                                <button type="button" @click="if(pin.length < 4) pin += '5'"
                                                    class="py-3 bg-gray-100 rounded-lg text-lg font-bold text-gray-800 hover:bg-gray-200 active:scale-95 transition">5</button>
                                                <button type="button" @click="if(pin.length < 4) pin += '6'"
                                                    class="py-3 bg-gray-100 rounded-lg text-lg font-bold text-gray-800 hover:bg-gray-200 active:scale-95 transition">6</button>
                                                <button type="button" @click="if(pin.length < 4) pin += '7'"
                                                    class="py-3 bg-gray-100 rounded-lg text-lg font-bold text-gray-800 hover:bg-gray-200 active:scale-95 transition">7</button>
                                                <button type="button" @click="if(pin.length < 4) pin += '8'"
                                                    class="py-3 bg-gray-100 rounded-lg text-lg font-bold text-gray-800 hover:bg-gray-200 active:scale-95 transition">8</button>
                                                <button type="button" @click="if(pin.length < 4) pin += '9'"
                                                    class="py-3 bg-gray-100 rounded-lg text-lg font-bold text-gray-800 hover:bg-gray-200 active:scale-95 transition">9</button>
                                                <button type="button" @click="pin = ''"
                                                    class="py-3 bg-red-50 hover:bg-red-100 rounded-lg text-xs font-bold text-red-600 active:scale-95 transition">Clear</button>
                                                <button type="button" @click="if(pin.length < 4) pin += '0'"
                                                    class="py-3 bg-gray-100 rounded-lg text-lg font-bold text-gray-800 hover:bg-gray-200 active:scale-95 transition">0</button>
                                                <button type="submit" :disabled="pin.length < 4"
                                                    class="py-3 bg-[#0F6E8C] hover:bg-[#0c5972] rounded-lg text-xs font-bold text-white disabled:opacity-40 disabled:cursor-not-allowed active:scale-95 transition">Enter</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </form>
            </div>

            <div class="hidden p-10 sm:flex flex-col items-center justify-center">

                <div class="text-center mb-3">
                    <h3 class="text-2xl font-bold text-p mb-1 flex justify-center gap-1">
                        Blue <label class="text-gray-800">POS System</label>
                    </h3>

                    <p class="text-gray-500 text-xs max-w-sm">
                        Manage inventory, sales, reports and customers
                        from one centralized dashboard.
                    </p>
                </div>

                <img src="{{ asset('images/thumbnail.png') }}" alt="POS Preview" class="w-full max-w-sm object-cover"
                    onerror="this.style.display='none'">

            </div>
        </div>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');

        form.addEventListener('submit', function(e) {
            // Disable button
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');

            // Show spinner, hide text
            btnText.classList.add('hidden');
            btnSpinner.classList.remove('hidden');
        });
    });
</script>
