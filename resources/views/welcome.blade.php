<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@lang('ui.title')</title>

        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="antialiased">
        <div class="justify-center flex items-center w-screen h-screen">
            <h1 class="text-4xl font-semibold">@lang('ui.welcome')</h1>
        </div>
    </body>
</html>
