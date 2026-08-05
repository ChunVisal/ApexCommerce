@extends('layouts.cashier')

@section('content')
    @include('cashier.orders.scripts')
    <div x-data="orderPage()">
        <div class="p-5">
            @include('cashier.orders.header-cards')
            @include('cashier.orders.filters-table')
            @include('cashier.orders.refund')
        </div>
        @include('cashier.pos.receipt')
    </div>
@endsection
