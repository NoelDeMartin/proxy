@extends('layouts.main')

@php
    $snippet = file_get_contents(base_path('resources/snippets/fetch.js'));
    $snippet = str_replace(':endpoint', url('/fetch'), $snippet);
@endphp

@push('styles')
    <link href="/css/prism.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="prose">
        <h1>@lang('ui.fetch.title')</h1>
        <p>@lang('ui.fetch.description')</p>
        <p class="mb-0">@lang('ui.fetch.example')</p>
        @include('snippets.fetch')
        <a href="/">@lang('ui.fetch.go_home')</a>
    </div>
@endsection
