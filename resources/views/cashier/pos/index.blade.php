@extends('layouts.cashier')

@section('content')
    @include('cashier.pos.scripts')
    <div class="flex gap-4" x-data="posPage()">
        {{-- LEFT: Products 75% --}}
        <div class="flex-1 min-w-0 py-5 pl-5 pr-3 ">
            @include('cashier.pos.product-grid')
        </div>

        {{-- RIGHT: Cart 25% - Sticky --}}
        <div class="w-[25%] min-w-[280px] flex-shrink-0">
            <div class="sticky top-20">
                @include('cashier.pos.cart-panel')
            </div>
        </div>

        @include('cashier.pos.checkout-slideover')
        @include('cashier.pos.receipt')
        @include('cashier.pos.customer-slideover')
    </div>
@endsection
