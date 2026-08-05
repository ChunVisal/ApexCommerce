@extends('layouts.app')

@php
    $uomCounts = [];
    foreach ($products as $p) {
        foreach ($p->uoms as $u) {
            if (!isset($uomCounts[$u->name])) {
                $uomCounts[$u->name] = 0;
            }
            $uomCounts[$u->name]++;
        }
    }
@endphp

@section('content')
    @include('admin.products-uom.scripts')
    <div class="w-full p-5 bg-gray-100/80 dark:bg-black transition-colors duration-300" x-data="productUomPage()">
        @include('admin.products-uom.filters-table')
        @include('admin.products-uom.slide-over')
    </div>
@endsection
