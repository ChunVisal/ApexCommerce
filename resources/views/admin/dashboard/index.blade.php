@extends('layouts.app')

@section('content')
    @include('admin.dashboard.scripts')
    <div class="w-full p-5 bg-gray-100/80 dark:bg-black transition-colors duration-300">
        @include('admin.dashboard.header-cards')
        @include('admin.dashboard.charts')
        @include('admin.dashboard.table')
    </div>
@endsection
