{{-- app/resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-white border-b px-6 py-4">
        <a href="/" class="text-xl font-semibold text-gray-800">📷 Document Photos</a>
    </header>
    <main class="max-w-2xl mx-auto py-10 px-4">
        @yield('content')
    </main>
    <footer class="text-center text-sm text-gray-400 py-6">
        <a href="{{ route('privacy-policy') }}" class="underline">Privacy Policy</a>
    </footer>
    @livewireScripts
</body>
</html>
