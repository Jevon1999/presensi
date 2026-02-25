<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Absensi App'))</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />

    {{-- Styles --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        
        {{-- Logo / Branding --}}
        <div class="mb-6">
            <a href="/">
                <img src="{{ asset('images/logo.svg') }}" alt="Logo" class="w-20 h-20" />
            </a>
        </div>

        {{-- Auth Card Container --}}
        <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-md overflow-hidden sm:rounded-lg">
            
            {{-- Flash Messages --}}
            @include('partials.alerts')

            {{-- Content --}}
            @yield('content')
        </div>

        {{-- Footer Links (Optional) --}}
        <div class="mt-6 text-center text-sm text-gray-600">
            @yield('footer-links')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
