@extends('layouts.app')

@section('content')
    @include('admin.partials.products.scripts')
    <div class="w-full p-5 bg-gray-100/80 dark:bg-black transition-colors duration-300" x-data="productPage()">

        {{-- <x-skeleton.product> --}}

        @include('admin.partials.products.header-filters')
        @include('admin.partials.products.slide-over')

        {{-- </x-skeleton.product> --}}
    </div>
@endsection
