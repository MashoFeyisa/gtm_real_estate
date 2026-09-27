<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'RealEstate')</title>
    <meta name="description" content="@yield('description', 'Find your dream property with our trusted real estate platform.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-[#f6f1e8] text-slate-800 antialiased flex min-h-screen flex-col">
    @include('layouts.partials.header')

    <main class="flex-1">
        @yield('content')
    </main>

    @include('layouts.partials.footer')

    @stack('scripts')
</body>
</html>
