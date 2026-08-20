 @extends('layouts.app')

 @section('content')
     @include('components.customers.scripts')
     <div class="w-full p-5" x-data="customerPage()">
         @include('components.customers.header-cards', [
             'exportRoute' => route('admin.customers.export'),
         ])
         @include('components.customers.filters-table')
         @include('components.customers.customer-detail')
         @include('cashier.pos.receipt')
     </div>
 @endsection
