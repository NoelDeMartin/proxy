<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>@lang('ui.title')</title>

        {{-- TODO implement cache-busting --}}
        <link href="/css/main.css" rel="stylesheet" />

        @stack('styles')
    </head>
    <body class="bg-gray-100 antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-6 py-4">
            @yield('content')
        </div>
    </body>
</html>
