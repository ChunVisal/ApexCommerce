 @extends('layouts.app')

 @section('content')
     <div class="w-full p-5 bg-gray-100/80 dark:bg-black transition-colors duration-300" x-data="customerPage()">
         @include('admin.customers.header-cards')
         @include('admin.customers.filters-table')
         @include('cashier.customers.customer-detail')
         @include('cashier.pos.receipt')
     </div>
     @include('admin.customers.scripts')
 @endsection
