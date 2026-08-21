<template x-teleport="body">
    {{-- resources/views/cashier/partials/pos/receipt.blade.php --}}
    <div id="printReceiptRoot" x-show="receiptOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="display: none;">
        {{-- Overlay --}}
        <div @click="receiptOpen = false" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

        {{-- Receipt Card --}}
        <div id="receiptCard"
            class="relative w-full max-w-sm bg-white dark:bg-zinc-900 rounded-lg shadow-2xl overflow-hidden">

            {{-- Receipt Paper --}}
            <div id="receiptPaper" class="p-6 font-mono text-sm">

                {{-- Refund Ribbon --}}
                <template x-if="receiptData.status === 'refunded'">
                    <div class="pointer-events-none">
                        <div class="absolute right-[-52px] top-4 w-[170px] transform rotate-45 z-50">
                            <div
                                class="bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200 py-1 font-bold text-xs text-center shadow-md border border-red-200 dark:border-red-900 select-none">
                                REFUNDED
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Header --}}
                <div class=" text-center mb-4">
                    @php $logo = App\Models\Setting::get('logo'); @endphp

                    @if ($logo)
                        <img src="{{ $logo }}" class="mx-auto h-12 w-auto mb-2" />
                    @else
                        <img src="{{ asset('images/logo.png') }}" class="mx-auto h-12 w-auto mb-2 dark:hidden" />
                        <img src="{{ asset('images/logodarkmode.png') }}"
                            class="mx-auto h-12 w-auto mb-2 hidden dark:block" />
                    @endif
                    <p class="text-[10px] text-gray-500 dark:text-zinc-300 mt-0.5">
                        {{ App\Models\Setting::get('shop_address', '123 Monivong Blvd, Phnom Penh') }}
                    </p>
                    <p class="text-[10px] text-gray-500 dark:text-zinc-300">Tel:
                        {{ App\Models\Setting::get('shop_phone', '012 345 678') }}</p>
                    <div class="border-t border-dashed border-gray-300 dark:border-zinc-700 my-3"></div>

                </div>

                {{-- Order Info --}}
                <div class="space-y-1 text-xs mb-3">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-zinc-300">Order:</span>
                        <span class="font-semibold text-gray-800 dark:text-zinc-200"
                            x-text="lastOrder.order_number"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-zinc-300">Date:</span>
                        <span class="text-gray-800 dark:text-zinc-200" x-text="new Date().toLocaleDateString()"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-zinc-300">Time:</span>
                        <span class="text-gray-800 dark:text-zinc-200" x-text="new Date().toLocaleTimeString()"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-zinc-300">Cashier:</span>
                        <span class="text-gray-800 dark:text-zinc-200">{{ auth()->user()->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-zinc-300">Customer:</span>
                        <span class="text-gray-500 dark:text-zinc-300"
                            x-text="(receiptData.customer?.name || 'Cash') + ' - ' + (receiptData.payment_method === 'cash' ? 'Cash' : receiptData.payment_method === 'card' ? 'Card' : 'KHQR')"></span>
                    </div>
                </div>

                <div class="border-t border-dashed border-gray-300 dark:border-zinc-700 my-3"></div>

                {{-- Items --}}
                <div class="space-y-1.5 mb-3">
                    <template x-for="item in receiptData.items" :key="item.id">
                        <div class="flex justify-between text-xs" :class="item.is_refunded ? 'opacity-70' : ''">
                            <span class="flex-1 truncate dark:text-zinc-200">
                                <span x-text="item.qty"></span>x <span x-text="item.name"></span>
                                <span class="text-[12px] text-gray-800 dark:text-white"
                                    x-text="item.base_unit ? '(' + item.base_unit + ')' : '' "></span>

                            </span>
                            <span class="font-semibold ml-2"
                                :class="item.is_refunded ? 'line-through text-red-500 dark:text-red-400' : 'dark:text-zinc-200'">
                                $<span x-text="(item.price * item.qty).toFixed(2)"></span>
                            </span>
                        </div>
                    </template>
                </div>

                <div class="border-t border-dashed border-gray-300 dark:border-zinc-700 my-3"></div>

                {{-- Totals --}}
                <div class="flex justify-between dark:text-zinc-300">
                    <span class="text-gray-500 dark:text-zinc-300">Subtotal</span>
                    <span>$<span x-text="receiptData.subtotal?.toFixed(2) || '0.00'"></span></span>
                </div>
                <div class="flex justify-between dark:text-zinc-300" x-show="receiptData.discount > 0">
                    <span class="text-gray-500 dark:text-zinc-300">Discount</span>
                    <span>-$<span x-text="(receiptData.discount || 0).toFixed(2)"></span></span>
                </div>
                <div x-show="receiptData.vip_discount > 0" class="flex justify-between text-sm text-yellow-600">
                    <span>VIP Discount (5%)</span>
                    <span>-$<span x-text="(receiptData.vip_discount || 0).toFixed(2)"></span></span>
                </div>
                <div class="flex justify-between dark:text-zinc-300">
                    <span class="text-gray-500 dark:text-zinc-300">Net</span>
                    <span>$<span x-text="receiptData.net?.toFixed(2) || '0.00'"></span></span>
                </div>
                <div class="flex justify-between ">
                    <span class="text-gray-500  dark:text-zinc-300">Tax
                        {{ App\Models\Setting::get('tax_rate', '10') }}%</span>
                    <span class="dark:text-zinc-200">$<span
                            x-text="receiptData.tax?.toFixed(2) || '0.00'"></span></span>
                </div>
                <div class="flex justify-between text-base font-bold border-t pt-1 mt-1 dark:text-zinc-200">
                    <span>TOTAL</span>
                    <span class="text-[#0F6E8C]">$<span x-text="receiptData.total?.toFixed(2) || '0.00'"></span></span>
                </div>

                <div class="border-t border-dashed border-gray-300 dark:border-zinc-700 my-2"></div>

                {{-- Payment --}}
                <span class="dark:text-zinc-300 font-semibold capitalize" x-text="receiptData.payment_method"></span>
                <span class="dark:text-zinc-300" x-show="receiptData.payment_method === 'cash'">$<span
                        x-text="parseFloat(receiptData.amount_received || 0).toFixed(2)"></span></span>
                <span class="dark:text-zinc-300" x-show="receiptData.change > 0">Change $<span
                        x-text="receiptData.change?.toFixed(2)"></span></span>

                <div class="border-t border-dashed border-gray-300 dark:border-zinc-700 my-3"></div>

                {{-- Footer --}}
                <div class="text-center text-[10px] text-gray-400 space-y-1">
                    <div class="flex items-center justify-center gap-1.5 mb-1">
                        <span class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center">
                            <i class="fa-solid fa-check text-[10px] text-white"></i>
                        </span>
                        <span class="text-green-600 dark:text-green-400 font-medium">Payment Completed</span>
                    </div>
                    <p>Thank you for your purchase!</p>
                </div>

                {{-- Barcode --}}
                <div class="text-center mt-2">
                    <svg id="barcode" class="mx-auto"></svg>
                </div>
            </div>

            {{-- Actions --}}
            <div id="receiptPrint"
                class="no-print px-6 py-4 border-t border-gray-200 dark:border-zinc-800 flex gap-3 bg-gray-50 dark:bg-zinc-800/50">
                <button
                    @click="receiptOpen = false; cartItems = []; amountReceived = ''; change = 0; paymentMethod = 'cash';"
                    class="flex-1 py-2 text-xs font-semibold text-white bg-[#0F6E8C] rounded-md hover:bg-[#0c5972] transition">
                    <i class="fa-solid fa-check mr-1"></i> Done
                </button>
                <button @click="window.print()"
                    class="flex-1 py-2 text-xs font-semibold text-gray-600 dark:text-zinc-300 border border-gray-300 dark:border-zinc-600 rounded-md hover:bg-white dark:hover:bg-zinc-700 transition">
                    <i class="fa-solid fa-print mr-1"></i> Print
                </button>
            </div>
        </div>
    </div>
</template>
<style>
    @media print {

        /* Hide every direct child of body except the teleported receipt root */
        body>*:not(#printReceiptRoot) {
            display: none !important;
        }

        #printReceiptRoot {
            display: block !important;
            position: static !important;
            background: none !important;
            padding: 0 !important;
        }

        .no-print {
            display: none !important;
        }

        #receiptCard {
            width: 80mm;
            max-width: 80mm;
            margin: 0 auto;
            box-shadow: none !important;
            border-radius: 0 !important;
            background: white !important;
        }

        #receiptPaper {
            width: 100%;
            padding: 4mm;
            font-size: 11px;
            color: #000 !important;
        }

        #receiptPaper * {
            color: #000 !important;
            background: transparent !important;
        }

        @page {
            size: 80mm auto;
            margin: 0;
        }
    }
</style>
