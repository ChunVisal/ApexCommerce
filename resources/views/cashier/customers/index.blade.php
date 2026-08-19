@extends('layouts.cashier')

@section('content')
    @include('cashier.customers.scripts')
    <div class="w-full p-5" x-data="customerPage()">
        @include('components.customers.header-cards', [
            'exportRoute' => route('admin.customers.export'),
        ])
        @include('cashier.customers.filters-table')
        @include('cashier.customers.customer-detail')
        @include('cashier.pos.receipt')
    </div>
@endsection
