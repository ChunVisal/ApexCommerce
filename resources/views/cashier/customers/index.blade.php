@extends('layouts.cashier')

@section('content')
    @include('cashier.customers.scripts')
    <div class="w-full p-5" x-data="customerPage()">
        @include('cashier.customers.header-cards')
        @include('cashier.customers.filters-table')
        @include('cashier.customers.customer-detail')
        @include('cashier.pos.receipt')
        @include('cashier.pos.customer-slideover')
    </div>
@endsection
