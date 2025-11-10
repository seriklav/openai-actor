<!DOCTYPE html>
<html lang="{{ str_replace('_','-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Laravel'))</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-neutral-50 text-neutral-900 antialiased">
@include('layouts._navbar')

<main class="container mx-auto max-w-5xl px-4 py-8">
    @include('layouts._flash')
    @yield('content')
</main>

@stack('modals')
@stack('scripts')
</body>
</html>
