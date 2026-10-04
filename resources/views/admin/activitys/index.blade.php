@extends('layouts.app')

@section('content')
    @include('admin.activitys.scripts')
    <div x-data="activityPage()">
        @include('admin.activitys.header-cards')
        @include('admin.activitys.filters-table')
    </div>
@endsection
