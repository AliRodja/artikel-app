<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Beranda') · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-gray-50 text-gray-900 antialiased">

    @include('partials.navbar')

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
        @include('partials.flash')
        @yield('content')
    </main>

    <footer class="border-t border-gray-200 bg-white py-4 text-center text-sm text-gray-500">
        &copy; {{ date('Y') }} {{ config('app.name') }}
    </footer>

    @stack('scripts')
</body>
</html>