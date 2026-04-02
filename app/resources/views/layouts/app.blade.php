{{-- app/resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'SnapDoc') }} — Free Passport & Visa Photo Maker</title>
    <meta name="description" content="Create perfect passport and visa photos in seconds. Free, no registration required. AI-powered background removal and precise document format sizing.">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @livewireStyles
    <style>
    /* Grid background */
    .grid-bg {
        position: fixed;
        inset: 0;
        z-index: 0;
        background-image:
            linear-gradient(rgba(99,102,241,0.06) 1px, transparent 1px),
            linear-gradient(90deg, rgba(99,102,241,0.06) 1px, transparent 1px);
        background-size: 60px 60px;
        pointer-events: none;
    }

    /* Gradient orbs */
    .orb {
        position: fixed;
        border-radius: 50%;
        filter: blur(60px);
        pointer-events: none;
        z-index: 0;
    }
    .orb-1 {
        width: 600px; height: 600px;
        top: -200px; left: -100px;
        background: radial-gradient(circle, rgba(99,102,241,0.15), transparent 70%);
        animation: float 8s ease-in-out infinite;
    }
    .orb-2 {
        width: 500px; height: 500px;
        bottom: -100px; right: -100px;
        background: radial-gradient(circle, rgba(167,139,250,0.12), transparent 70%);
        animation: float 10s ease-in-out infinite reverse;
    }
    .orb-3 {
        width: 400px; height: 400px;
        top: 40%; left: 50%;
        background: radial-gradient(circle, rgba(96,165,250,0.10), transparent 70%);
        animation: float 12s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(30px, -30px) scale(1.05); }
        66% { transform: translate(-20px, 20px) scale(0.95); }
    }

    /* Animated gradient text */
    .gradient-text {
        background: linear-gradient(135deg, #6366f1, #a78bfa, #60a5fa);
        background-size: 200%;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        animation: gradient-shift 6s ease infinite;
    }

    @keyframes gradient-shift {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    /* Gradient background (for buttons/icons) */
    .gradient-bg {
        background: linear-gradient(135deg, #6366f1 0%, #a78bfa 100%);
    }

    /* Card glow on hover */
    .card-glow {
        transition: all 0.3s;
    }
    .card-glow:hover {
        border-color: rgba(99,102,241, 0.2);
        box-shadow: 0 0 30px rgba(99,102,241, 0.08), 0 4px 16px rgba(0,0,0,0.04);
    }

    /* Fade-in animations */
    .fade-in {
        animation: fadeIn 0.6s ease-out forwards;
        opacity: 0;
    }
    .fade-in-delay-1 { animation-delay: 0.1s; }
    .fade-in-delay-2 { animation-delay: 0.2s; }
    .fade-in-delay-3 { animation-delay: 0.3s; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
</head>
<body class="bg-[#fafafe] min-h-screen font-sans antialiased">
    {{-- Global background effects --}}
    <div class="grid-bg"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    {{-- Navigation --}}
    <nav class="relative z-10 max-w-6xl mx-auto px-4 py-5 flex items-center justify-between">
        <a href="/" class="text-xl font-extrabold gradient-text">SnapDoc</a>
        <div class="flex items-center gap-8">
            <a href="#how-it-works" class="text-sm font-medium text-gray-500 hover:text-indigo-500 transition">How It Works</a>
            <a href="#features" class="text-sm font-medium text-gray-500 hover:text-indigo-500 transition">Features</a>
            <a href="{{ route('privacy-policy') }}" class="text-sm font-medium text-gray-500 hover:text-indigo-500 transition">Privacy Policy</a>
        </div>
    </nav>

    @yield('content')

    <footer class="relative z-10 max-w-6xl mx-auto px-4 py-8 flex items-center justify-between border-t border-indigo-500/[0.08]">
        <p class="text-sm text-gray-400">&copy; {{ date('Y') }} {{ config('app.name', 'SnapDoc') }}. All rights reserved.</p>
        <a href="{{ route('privacy-policy') }}" class="text-sm text-gray-500 hover:text-indigo-500 transition">Privacy Policy</a>
    </footer>

    @livewireScripts
</body>
</html>
