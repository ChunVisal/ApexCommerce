@extends('layouts.cashier')

@section('content')
    @include('cashier.orders.scripts')
    <div x-data="orderPage()" x-data="orderSearch()">
        <div class="">
            @include('cashier.orders.header-cards')
            @include('cashier.orders.filters-table')
            @include('cashier.orders.refund')
        </div>
        @include('cashier.pos.receipt')
    </div>
@endsection
