@extends('layouts.app')

@section('content')
    @include('admin.inventory.scripts')
    <div class="w-full p-5 bg-gray-100/80 dark:bg-black transition-colors duration-300" x-data="inventoryPage()">
        @include('admin.inventory.header-charts-cards')
        @include('admin.inventory.filters-table')
        @include('admin.inventory.slide-over')
        @include('admin.inventory.stock-drop')
    </div>
@endsection
