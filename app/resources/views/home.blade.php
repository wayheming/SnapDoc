@extends('layouts.app')

@section('content')
    {{-- HERO SECTION with embedded form --}}
    <section class="relative overflow-hidden gradient-bg min-h-[90vh] flex items-center">
        <div class="blob-1 -top-40 -left-40"></div>
        <div class="blob-2 -bottom-32 -right-32"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 py-16 sm:py-20 w-full">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                {{-- Left: text --}}
                <div class="text-center lg:text-left">
                    <div class="fade-in">
                        <span class="inline-block px-4 py-1.5 rounded-full text-sm font-medium bg-white/15 text-white/90 mb-6 backdrop-blur-sm">
                            100% Free &mdash; No Registration Required
                        </span>
                    </div>

                    <h1 class="fade-in fade-in-delay-1 text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-6">
                        Perfect Document Photos
                        <br>
                        <span class="text-white/80">in Seconds</span>
                    </h1>

                    <p class="fade-in fade-in-delay-2 text-lg sm:text-xl text-white/75 max-w-lg mx-auto lg:mx-0 mb-10 leading-relaxed">
                        AI-powered passport and visa photo maker. Upload your photo, choose the document format, and download the result &mdash; completely free.
                    </p>

                    {{-- Stats --}}
                    <div class="fade-in fade-in-delay-3 grid grid-cols-3 gap-8 max-w-sm mx-auto lg:mx-0">
                        <div>
                            <p class="text-3xl font-bold text-white">Free</p>
                            <p class="text-sm text-white/60 mt-1">Always</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-white">&lt;30s</p>
                            <p class="text-sm text-white/60 mt-1">Processing</p>
                        </div>
                        <div>
                            <p class="text-3xl font-bold text-white">AI</p>
                            <p class="text-sm text-white/60 mt-1">Powered</p>
                        </div>
                    </div>
                </div>

                {{-- Right: upload form --}}
                <div class="fade-in fade-in-delay-2">
                    <div class="bg-white/95 backdrop-blur-lg rounded-2xl shadow-2xl shadow-black/10 p-6 sm:p-8 border border-white/20">
                        <livewire:photo-processor />
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom wave --}}
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="#F9FAFB"/>
            </svg>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section class="py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">How It Works</h2>
                <p class="text-gray-500 text-lg max-w-xl mx-auto">Three simple steps to get your perfect document photo</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                {{-- Step 1 --}}
                <div class="relative bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow duration-300 border border-gray-100">
                    <div class="w-14 h-14 rounded-xl gradient-bg flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>
                    <span class="absolute top-6 right-6 text-5xl font-bold text-gray-100">1</span>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Upload Photo</h3>
                    <p class="text-gray-500 leading-relaxed">Take a selfie or upload any photo with a clear face. No special background needed.</p>
                </div>

                {{-- Step 2 --}}
                <div class="relative bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow duration-300 border border-gray-100">
                    <div class="w-14 h-14 rounded-xl gradient-bg flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="absolute top-6 right-6 text-5xl font-bold text-gray-100">2</span>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Choose Format</h3>
                    <p class="text-gray-500 leading-relaxed">Select your document type &mdash; passport, visa, ID card. We support formats from many countries.</p>
                </div>

                {{-- Step 3 --}}
                <div class="relative bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition-shadow duration-300 border border-gray-100">
                    <div class="w-14 h-14 rounded-xl gradient-bg flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </div>
                    <span class="absolute top-6 right-6 text-5xl font-bold text-gray-100">3</span>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Download Free</h3>
                    <p class="text-gray-500 leading-relaxed">Our AI removes the background and sizes your photo perfectly. Download instantly.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- FEATURES --}}
    <section class="py-20 bg-white">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">Why Choose Us</h2>
                <p class="text-gray-500 text-lg max-w-xl mx-auto">Everything you need for the perfect document photo</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center p-6 rounded-2xl hover:bg-gray-50 transition">
                    <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Completely Free</h3>
                    <p class="text-sm text-gray-500">No hidden fees, no watermarks, no premium plans. Free forever.</p>
                </div>

                <div class="text-center p-6 rounded-2xl hover:bg-gray-50 transition">
                    <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">AI Background Removal</h3>
                    <p class="text-sm text-gray-500">Advanced neural network removes any background and replaces it with clean white.</p>
                </div>

                <div class="text-center p-6 rounded-2xl hover:bg-gray-50 transition">
                    <div class="w-12 h-12 rounded-full bg-pink-100 text-pink-600 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Multiple Countries</h3>
                    <p class="text-sm text-gray-500">Support for passport and visa formats from dozens of countries worldwide.</p>
                </div>

                <div class="text-center p-6 rounded-2xl hover:bg-gray-50 transition">
                    <div class="w-12 h-12 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Privacy First</h3>
                    <p class="text-sm text-gray-500">Photos are auto-deleted after processing. No accounts, no tracking.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- BOTTOM CTA --}}
    <section class="gradient-bg py-16">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Ready to Get Your Document Photo?</h2>
            <p class="text-white/70 text-lg mb-8">No signup, no payment. Just upload and download.</p>
            <a href="#"
               onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;"
               class="inline-flex items-center gap-2 bg-white text-indigo-700 px-8 py-4 rounded-xl text-lg font-semibold
                      hover:bg-gray-100 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-0.5">
                Get Started Now
            </a>
        </div>
    </section>
@endsection
