{{-- Checkout Modal --}}
<div x-show="checkoutOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
    {{-- Overlay --}}
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>

    {{-- Modal --}}
    <div class="relative w-full max-w-5xl h-[85vh] bg-white dark:bg-zinc-900 rounded-md shadow-2xl overflow-hidden flex">

        {{-- LEFT: Total + Methods --}}
        <div class="w-[35%] flex flex-col border-r border-gray-200 dark:border-zinc-800 p-6">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 bg-[#0F6E8C]/10 dark:bg-[#0F6E8C]/20 rounded-md flex items-center justify-center">
                        <i class="fa-solid fa-shield-halved text-blue-500 text-xl"></i>
                    </div>
                    <div class="flex flex-col">
                        <h2 class="leader-none text-base font-bold text-gray-800 dark:text-zinc-100">Secure Checkout
                        </h2>
                        <span class="text-xs text-gray-500 dark:text-z">Gateway Terminal</span>
                    </div>
                </div>
                <button @click="checkoutOpen = false"
                    class="w-8 h-8 rounded-full bg-gray-100 dark:bg-zinc-800 flex items-center justify-center text-gray-500 dark:text-z hover:bg-gray-200 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Total - Big Display --}}
            <div class=" bg-gray-100/60 dark:bg-zinc-800/70 rounded-lg p-4 text-start">
                <p class="text-xs text-gray-500 dark:text-z dark:text-zinc-300 mb-1">Total Amount</p>
                <p class="text-4xl font-bold text-[#0F6E8C]">$<span x-text="total.toFixed(2)"></span></p>
                <div class="flex justify-start gap-4 mt-2 mb-1 text-xs text-gray-500 dark:text-z dark:text-zinc-300">
                    <span>Subtotal: $<span x-text="subtotal.toFixed(2)"></span></span>
                    <span>Tax: $<span x-text="tax.toFixed(2)"></span></span>
                    {{-- Show discount amount (Only displays if active savings are calculated) --}}
                    <div class="flex items-center justify-between text-xs font-bold" x-show="discount > 0" x-cloak>
                        <span class="text-red-600 dark:text-red-400">
                            -$<span x-text="manualDiscount.toFixed(2)"></span>
                        </span>
                    </div>
                </div>

                {{-- VIP Discount (auto) --}}
                <div x-show="isVipCustomer"
                    class="mb-2 flex items-center justify-between text-xs text-yellow-600 bg-yellow-50 dark:bg-yellow-950/30 rounded p-2">
                    <span><i class="fa-solid fa-crown mr-1"></i> VIP 5% Discount</span>
                    <span>-$<span x-text="vipDiscount.toFixed(2)"></span></span>
                </div>
                {{-- Change --}}
                <div x-show="paymentMethod === 'cash' && change > 0"
                    class="mt-2 bg-green-50 dark:bg-green-950/30 rounded p-2">
                    <span class="text-sm text-green-700 dark:text-green-400">Change: $<span
                            x-text="change.toFixed(2)"></span></span>
                </div class="">


            </div>

            {{-- In checkout modal, after subtotal/tax, before total --}}
            <div class="py-1 my-3 border-t border-b border-gray-100 dark:border-zinc-800/60">
                {{-- Discount Control Field Block --}}
                <div class="flex items-center justify-between">
                    <span
                        class="text-[11px] font-bold text-gray-500 dark:text-zinc-300 uppercase tracking-wider">Discount</span>
                    <div class="flex items-center gap-1.5">
                        {{-- Numerical Discount Input --}}
                        <input type="number" x-model="discountValue" placeholder="0" min="0"
                            class="w-16 text-xs font-semibold text-right border border-gray-250 dark:border-zinc-800/80 rounded-md px-2 py-1 bg-white dark:bg-zinc-950 text-gray-900 dark:text-zinc-100 focus:outline-none focus:border-[#0F6E8C] dark:focus:border-[#1389af] transition-colors [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">

                        {{-- Discount Type Toggle Selector --}}
                        <div class="relative">
                            <select x-model="discountType"
                                class="text-xs font-semibold border border-gray-250 dark:border-zinc-800/80 rounded-md pl-2 pr-6 py-1 bg-white dark:bg-zinc-950 text-gray-900 dark:text-zinc-100 focus:outline-none focus:border-[#0F6E8C] dark:focus:border-[#1389af] appearance-none transition-colors">
                                <option value="fixed">$</option>
                                <option value="percent">%</option>
                            </select>
                            <x-heroicon-o-chevron-down
                                class="w-3 h-3 absolute right-2 top-1/2 -translate-y-1/2 text-gray-450 dark:text-zinc-500 pointer-events-none" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Payment Methods - Vertical List --}}
            <div class="flex-1 space-y-3">
                <p class="text-xs font-semibold text-gray-500 dark:text-zinc-300 uppercase">Select Payment</p>

                <button @click="paymentMethod = 'cash'; amountReceived = ''; change = 0"
                    :class="paymentMethod === 'cash' ? 'border-[#0F6E8C]/60 bg-[#0F6E8C]/5' :
                        'border-gray-200 dark:border-zinc-700'"
                    class="w-full flex items-center gap-3 p-3 rounded-lg border-2 transition-all">
                    <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-950 flex items-center justify-center">
                        <i class="fa-solid fa-money-bill text-green-600"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-bold text-gray-800 dark:text-zinc-200">Cash Payment</p>
                        <p class="text-xs text-gray-500 dark:text-zinc-300">Walk-in payment</p>
                    </div>
                </button>

                <button @click="paymentMethod = 'card'"
                    :class="paymentMethod === 'card' ? 'border-[#0F6E8C]/60 bg-[#0F6E8C]/5' :
                        'border-gray-200 dark:border-zinc-700'"
                    class="w-full flex items-center gap-3 p-3 rounded-lg border-2 transition-all">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-950 flex items-center justify-center">
                        <i class="fa-solid fa-credit-card text-blue-600"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-bold text-gray-800 dark:text-zinc-200">Credit Card</p>
                        <p class="text-xs text-gray-500 dark:text-z">Credit / Debit</p>
                    </div>
                </button>

                <button @click="paymentMethod = 'khqr'; startTimer()"
                    :class="paymentMethod === 'khqr' ? 'border-[#0F6E8C]/60 bg-[#0F6E8C]/5' :
                        'border-gray-200 dark:border-zinc-700'"
                    class="w-full flex items-center gap-3 p-3 rounded-lg border-2 transition-all">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-950 flex items-center justify-center">
                        <i class="fa-solid fa-qrcode text-purple-600"></i>
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-bold text-gray-800 dark:text-zinc-200">Bakong KHQR</p>
                        <p class="text-xs text-gray-500 dark:text-z">Scan to pay</p>
                    </div>
                </button>

                <div x-show="requiresCustomer && !selectedCustomer"
                    class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 rounded-lg p-3 mb-3">
                    <p class="text-xs text-amber-700 dark:text-amber-400">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                        Orders over $700 require customer information.
                    </p>
                </div>
            </div>
        </div>

        {{-- RIGHT: Interactive Display --}}
        <div class="flex-1 flex flex-col bg-gray-50 dark:bg-zinc-800/50">
            {{-- title --}}
            <div class="p-4 flex justify-between bg-slate-800 dark:bg-zinc-900/50">
                <div class="">
                    <h3 class="text-md font-semibold text-gray-200 dark:text-zinc-200"
                        x-text="paymentMethod === 'cash' ? 'Cash Payment' : paymentMethod === 'card' ? 'Card Terminal' : 'KHQR Scan'">
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-zinc-200"
                        x-text="paymentMethod === 'cash' ? 'Enter amount received from customer' : paymentMethod === 'card' ? 'Tap or swipe card to process' : 'Customer scans QR code to pay'">
                    </p>
                </div>
                <button @click="customerOpen = true"
                    class="bg-p px-3 py-1.5 text-xs text-white rounded-md flex items-center gap-1">
                    <i class="fa-solid fa-user-plus"></i>
                    <span x-text="selectedCustomer ? selectedCustomer.name : 'Add Customer'"></span>
                </button>
            </div>
            {{-- Display Area --}}
            <div class="flex-1 flex items-center justify-center p-8">

                {{-- CASH: Calculator - Compact --}}
                <div x-show="paymentMethod === 'cash'" class="w-full max-w-xs">
                    <div
                        class= "dark:text-zinc-300 dark:bg-zinc-900 rounded-lg p-3 mb-3 border border-gray-200 dark:border-zinc-800">
                        <p class="text-[10px] text-gray-500 dark:text-z mb-0.5">Amount Received</p>
                        <input type="text" inputmode="decimal" x-model="amountReceived"
                            @focus="if(!amountReceived) amountReceived = total.toFixed(2); calculateChange()"
                            @input="calculateChange()"
                            class="w-full text-3xl font-bold text-center
                             text-gray-800 dark:text-zinc-200 border-gray-200 dark:border-zinc-700 focus:ring-0 outline-none bg-transparent"
                            placeholder="0.00">
                    </div>
                    <div class="grid grid-cols-3 gap-1.5">
                        <button @click="appendAmount('1')"
                            class="py-3 dark:text-zinc-300 bg-gray-200/30 dark:bg-zinc-900 rounded-lg text-base font-bold hover:bg-gray-100 dark:hover:bg-zinc-800 transition">1</button>
                        <button @click="appendAmount('2')"
                            class="py-3 dark:text-zinc-300 bg-gray-200/30 dark:bg-zinc-900 rounded-lg text-base font-bold hover:bg-gray-100 dark:hover:bg-zinc-800 transition">2</button>
                        <button @click="appendAmount('3')"
                            class="py-3 dark:text-zinc-300 bg-gray-200/30 dark:bg-zinc-900 rounded-lg text-base font-bold hover:bg-gray-100 dark:hover:bg-zinc-800 transition">3</button>
                        <button @click="appendAmount('4')"
                            class="py-3 dark:text-zinc-300 bg-gray-200/30 dark:bg-zinc-900 rounded-lg text-base font-bold hover:bg-gray-100 dark:hover:bg-zinc-800 transition">4</button>
                        <button @click="appendAmount('5')"
                            class="py-3 dark:text-zinc-300 bg-gray-200/30 dark:bg-zinc-900 rounded-lg text-base font-bold hover:bg-gray-100 dark:hover:bg-zinc-800 transition">5</button>
                        <button @click="appendAmount('6')"
                            class="py-3 dark:text-zinc-300 bg-gray-200/30 dark:bg-zinc-900 rounded-lg text-base font-bold hover:bg-gray-100 dark:hover:bg-zinc-800 transition">6</button>
                        <button @click="appendAmount('7')"
                            class="py-3 dark:text-zinc-300 bg-gray-200/30 dark:bg-zinc-900 rounded-lg text-base font-bold hover:bg-gray-100 dark:hover:bg-zinc-800 transition">7</button>
                        <button @click="appendAmount('8')"
                            class="py-3 dark:text-zinc-300 bg-gray-200/30 dark:bg-zinc-900 rounded-lg text-base font-bold hover:bg-gray-100 dark:hover:bg-zinc-800 transition">8</button>
                        <button @click="appendAmount('9')"
                            class="py-3 dark:text-zinc-300 bg-gray-200/30 dark:bg-zinc-900 rounded-lg text-base font-bold hover:bg-gray-100 dark:hover:bg-zinc-800 transition">9</button>
                        <button @click="appendAmount('.')"
                            class="py-3 dark:text-zinc-300 bg-gray-200/30 dark:bg-zinc-900 rounded-lg text-base font-bold hover:bg-gray-100 dark:hover:bg-zinc-800 transition">.</button>
                        <button @click="appendAmount('0')"
                            class="py-3 dark:text-zinc-300 bg-gray-200/30 dark:bg-zinc-900 rounded-lg text-base font-bold hover:bg-gray-100 dark:hover:bg-zinc-800 transition">0</button>
                        <button @click="backspaceAmount()"
                            class="py-3 bg-amber-50 dark:bg-amber-950/30 rounded-lg text-sm font-bold text-amber-600">
                            <i class="fa-solid fa-delete-left"></i>
                        </button>
                    </div>
                </div>

                {{-- CARD --}}
                <div x-show="paymentMethod === 'card'" class="text-center">

                    <div @click="processPayment()"
                        class="relative w-100 h-48 mx-auto rounded-2xl overflow-hidden cursor-pointer transition-all duration-300 hover:scale-105 hover:shadow-2xl bg-gradient-to-br from-slate-900 via-slate-800 to-blue-900 text-white">

                        <!-- Background Decoration -->
                        <div class="absolute -top-16 -right-16 w-48 h-48 bg-blue-500/20 rounded-full blur-3xl"></div>
                        <div class="absolute -bottom-20 -left-16 w-48 h-48 bg-cyan-400/10 rounded-full blur-3xl"></div>

                        <!-- Card Content -->
                        <div class="relative h-full flex flex-col justify-between p-6">

                            <!-- Top -->
                            <div class="flex justify-between items-start">
                                <div>
                                    <h2 class="text-lg font-bold tracking-wide">BLUE POS</h2>
                                    <p class="text-xs text-gray-300">
                                        Secure Payment
                                    </p>
                                </div>

                                <!-- Contactless -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-gray-300" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M7 8a7 7 0 019.9 0M10 11a3 3 0 014.2 0M13 14l.01.01" />
                                </svg>
                            </div>

                            <!-- Chip -->
                            <div
                                class="w-12 h-6 rounded-sm bg-gradient-to-br from-yellow-200 to-yellow-500 shadow-inner">
                            </div>

                            <!-- Card Number -->
                            <div>
                                <p class="tracking-[0.35em] text-xl font-semibold">
                                    •••• •••• •••• 4242
                                </p>

                                <div class="flex justify-between items-end mt-5">
                                    <div>
                                        <p class="text-[10px] uppercase text-gray-500">
                                            Card Holder
                                        </p>
                                        <p class="font-medium">
                                            CUSTOMER
                                        </p>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-[10px] uppercase text-gray-500">
                                            Expires
                                        </p>
                                        <p>12/30</p>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <!-- Swipe Animation -->
                        <div class="absolute inset-0 overflow-hidden rounded-2xl pointer-events-none">
                            <div
                                class="absolute top-0 -left-40 w-24 h-full bg-white/20 blur-xl rotate-12 animate-swipe">
                            </div>
                        </div>

                    </div>

                    <p class="mt-4 text-sm text-gray-500 dark:text-z">
                        Tap the card to complete payment
                    </p>

                </div>
                {{-- KHQR --}}
                <div x-show="paymentMethod === 'khqr'" class="text-center">
                    <p class="text-xs text-gray-500 dark:text-z mb-1">Expires: <span class="font-bold text-red-500"
                            x-text="formatTime(timer)"></span></p>
                    <div class="mx-auto bg-white py-1">
                        <img src="{{ asset('images/KHQR.jpg') }}" alt="KHQR Code"
                            class="w-[250px] h-[250px] object-contain">
                    </div>
                    <p class="text-xs text-gray-500 dark:text-z">KHQR • Scan to pay</p>
                    <div class="flex gap-1.5 justify-center mt-3">
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full animate-bounce"></span>
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full animate-bounce"
                            style="animation-delay: 0.2s"></span>
                        <span class="w-1.5 h-1.5 bg-purple-500 rounded-full animate-bounce"
                            style="animation-delay: 0.4s"></span>
                    </div>
                </div>
            </div>

            {{-- Bottom Buttons --}}
            <div class="flex gap-3 p-6 border-t border-gray-200 dark:border-zinc-700">
                <button @click="checkoutOpen = false"
                    class="flex-1 py-2.5 text-sm font-semibold  text-gray-600 dark:text-zinc-200 border border-gray-300 dark:border-zinc-700 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                    Cancel
                </button>
                <button @click="processPayment()"
                    :disabled="submitting || (paymentMethod === 'cash' && (Math.round(parseFloat(amountReceived || 0) * 100) /
                        100) < (Math.round(total * 100) / 100))"
                    class="flex-[2] py-2.5 text-sm font-bold text-white bg-[#0F6E8C] rounded-lg hover:bg-[#0c5972] disabled:opacity-50 transition">
                    <span
                        x-text="submitting ? 'Processing...' : requiresCustomer && !selectedCustomer ? 'Customer Info Required' : 'Complete Payment'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes swipe {
        0% {
            transform: translateX(-250%);
        }

        100% {
            transform: translateX(550%);
        }
    }

    .animate-swipe {
        animation: swipe 2.5s linear infinite;
    }
</style>
