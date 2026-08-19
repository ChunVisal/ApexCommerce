 @extends('layouts.app')

 @section('content')
     <div class="w-full p-5" x-data="customerPage()">
         @include('components.customers.header-cards', [
             'exportRoute' => route('admin.customers.export'),
         ])
         @include('admin.customers.filters-table')
         @include('cashier.customers.customer-detail')
         @include('cashier.pos.receipt')
     </div>
     @include('admin.customers.scripts')
 @endsection
