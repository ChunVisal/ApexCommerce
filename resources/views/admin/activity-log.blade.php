@extends('layouts.app')

@php
    use App\Helpers\ActivityData;
    $summaryCards = ActivityData::getSummaryCards();
@endphp

@section('content')
    @include('admin.partials.activitys.scripts')
    <div class="p-5" x-data="activityPage()">
        @include('admin.partials.activitys.header-cards')
        @include('admin.partials.activitys.filters-table')
    </div>
@endsection
