@extends('layouts.main')

@php
    $allowedOrigins = config('cors.allowed_origins');
    $isRestricted = ! Arr::has($allowedOrigins, '*');
@endphp

@section('content')
    <div class="prose">
        <h1>@lang('ui.welcome.title')</h1>
        <p>@lang('ui.welcome.description')</p>

        @if($isRestricted)
            <p>@lang('ui.welcome.restricted')</p>
            <ul>
                @foreach(config('cors.allowed_origins') as $origin)
                    <li>
                        <a href="{{ $origin }}" target="_blank">{{ $origin }}</a>
                    </li>
                @endforeach
            </ul>
        @endif

        <p class="mt-12 mb-2 text-sm">@lang('ui.welcome.open_source')</p>
        <div class="not-prose flex">
            <a
                href="https://github.com/noeldemartin/proxy"
                class="inline-flex items-center border border-transparent bg-gray-200 py-2 px-4 hover:bg-gray-300"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 512 512"
                    aria-hidden="true"
                    class="h-5 w-5"
                >
                    <path
                        d="M255.968,5.329C114.624,5.329,0,120.401,0,262.353c0,113.536,73.344,209.856,175.104,243.872
                            c12.8,2.368,17.472-5.568,17.472-12.384c0-6.112-0.224-22.272-0.352-43.712c-71.2,15.52-86.24-34.464-86.24-34.464
                            c-11.616-29.696-28.416-37.6-28.416-37.6c-23.264-15.936,1.728-15.616,1.728-15.616c25.696,1.824,39.2,26.496,39.2,26.496
                            c22.848,39.264,59.936,27.936,74.528,21.344c2.304-16.608,8.928-27.936,16.256-34.368
                            c-56.832-6.496-116.608-28.544-116.608-127.008c0-28.064,9.984-51.008,26.368-68.992c-2.656-6.496-11.424-32.64,2.496-68
                            c0,0,21.504-6.912,70.4,26.336c20.416-5.696,42.304-8.544,64.096-8.64c21.728,0.128,43.648,2.944,64.096,8.672
                            c48.864-33.248,70.336-26.336,70.336-26.336c13.952,35.392,5.184,61.504,2.56,68c16.416,17.984,26.304,40.928,26.304,68.992
                            c0,98.72-59.84,120.448-116.864,126.816c9.184,7.936,17.376,23.616,17.376,47.584c0,34.368-0.32,62.08-0.32,70.496
                            c0,6.88,4.608,14.88,17.6,12.352C438.72,472.145,512,375.857,512,262.353C512,120.401,397.376,5.329,255.968,5.329z"
                        fill="currentColor"
                    />
                </svg>
                <span class="ml-2">@lang('ui.welcome.github')</span>
            </a>
        </div>
    </div>
@endsection
