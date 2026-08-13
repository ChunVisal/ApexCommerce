@extends('layouts.cashier')

@section('content')
    @include('cashier.products.scripts')
    <div class="p-5" x-data="productPage()">
        @include('cashier.products.header-cards')
        @include('cashier.products.filters-table')
        @include('cashier.products.request-stock')
        @include('cashier.products.request-new-product')
        @include('cashier.products.report-stock-slideover')
    </div>
@endsection
