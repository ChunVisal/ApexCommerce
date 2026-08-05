@extends('layouts.app')

@section('content')
    @include('admin.users.scripts')
    <div class="w-full p-5 bg-gray-100/80 dark:bg-black transition-colors duration-300" x-data="userPage()">
        @include('admin.users.header-cards')
        @include('admin.users.filters-table')
        @include('admin.users.slide-over')
        @include('admin.users.users-detail')
    </div>
@endsection
