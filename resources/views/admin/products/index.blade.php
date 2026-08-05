@extends('layouts.app')

@section('content')
    @include('admin.products.scripts')
    <div class="w-full p-5 bg-gray-100/80 dark:bg-black transition-colors duration-300" x-data="productPage()">
        @include('admin.products.filters-table')
        @include('admin.products.slide-over')
    </div>
@endsection
