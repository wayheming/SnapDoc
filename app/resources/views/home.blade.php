@extends('layouts.app')

@section('content')
    {{-- HERO SECTION --}}
    <section class="relative z-10">
        <div class="max-w-6xl mx-auto px-4 py-16 sm:py-20">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                {{-- Left: text --}}
                <div class="text-center lg:text-left">
                    <div class="fade-in">
                        <span class="inline-block px-4 py-1.5 rounded-full text-sm font-semibold text-indigo-500 bg-indigo-500/[0.08] border border-indigo-500/[0.15] mb-6">
                            100% Free &mdash; No Registration Required
                        </span>
                    </div>

                    <h1 class="fade-in fade-in-delay-1 text-4xl sm:text-5xl lg:text-[52px] font-extrabold leading-tight mb-2">
                        <span class="gradient-text">Perfect Document Photos</span>
                    </h1>
                    <p class="fade-in fade-in-delay-1 text-3xl sm:text-4xl font-extrabold text-gray-400 mb-6">in Seconds</p>

                    <p class="fade-in fade-in-delay-2 text-lg text-gray-500 max-w-lg mx-auto lg:mx-0 mb-10 leading-relaxed">
                        AI-powered passport and visa photo maker. Upload your photo, choose the document format, and download the result &mdash; completely free.
                    </p>

                    {{-- Stats --}}
                    <div class="fade-in fade-in-delay-3 grid grid-cols-3 gap-8 max-w-sm mx-auto lg:mx-0">
                        <div>
                            <p class="text-3xl font-extrabold text-gray-900">Free</p>
                            <p class="text-sm text-gray-400 mt-1">Always</p>
                        </div>
                        <div>
                            <p class="text-3xl font-extrabold text-gray-900">&lt;30s</p>
                            <p class="text-sm text-gray-400 mt-1">Processing</p>
                        </div>
                        <div>
                            <p class="text-3xl font-extrabold text-gray-900">AI</p>
                            <p class="text-sm text-gray-400 mt-1">Powered</p>
                        </div>
                    </div>
                </div>

                {{-- Right: upload form --}}
                <div class="fade-in fade-in-delay-2">
                    <div class="bg-white rounded-[20px] p-6 sm:p-8 border border-indigo-500/[0.12] shadow-[0_0_40px_rgba(99,102,241,0.08),0_4px_24px_rgba(0,0,0,0.04)] hover:shadow-[0_0_60px_rgba(99,102,241,0.12),0_8px_32px_rgba(0,0,0,0.06)] transition-shadow duration-300">
                        <livewire:photo-processor />
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section id="how-it-works" class="relative z-10 py-20">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-14">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">How It Works</h2>
                <p class="text-gray-400 text-lg max-w-xl mx-auto">Three simple steps to get your perfect document photo</p>
            </div>

            <div class="grid md:grid-cols-3 gap-7">
                {{-- Step 1 --}}
                <div class="relative bg-white rounded-[20px] p-8 border border-indigo-500/[0.08] shadow-sm card-glow">
                    <div class="w-[52px] h-[52px] rounded-[14px] gradient-bg flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                    </div>
                    <span class="absolute top-4 right-5 text-[64px] font-extrabold text-indigo-500/[0.06]">1</span>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Upload Photo</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Take a selfie or upload any photo with a clear face. For best results, use a well-lit photo against a light background.</p>
                </div>

                {{-- Step 2 --}}
                <div class="relative bg-white rounded-[20px] p-8 border border-indigo-500/[0.08] shadow-sm card-glow">
                    <div class="w-[52px] h-[52px] rounded-[14px] gradient-bg flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <span class="absolute top-4 right-5 text-[64px] font-extrabold text-indigo-500/[0.06]">2</span>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Choose Format</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Select your document type &mdash; passport, visa, ID card. We support formats from many countries.</p>
                </div>

                {{-- Step 3 --}}
                <div class="relative bg-white rounded-[20px] p-8 border border-indigo-500/[0.08] shadow-sm card-glow">
                    <div class="w-[52px] h-[52px] rounded-[14px] gradient-bg flex items-center justify-center mb-5">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                    </div>
                    <span class="absolute top-4 right-5 text-[64px] font-extrabold text-indigo-500/[0.06]">3</span>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Download Free</h3>
                    <p class="text-gray-500 text-sm leading-relaxed">Our AI removes the background and sizes your photo perfectly. Download instantly.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- FEATURES --}}
    <section id="features" class="relative z-10 py-20">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center mb-14">
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">Why Choose Us</h2>
                <p class="text-gray-400 text-lg max-w-xl mx-auto">Everything you need for the perfect document photo</p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center p-7 rounded-[20px] transition-all duration-300 hover:bg-white hover:shadow-[0_0_30px_rgba(99,102,241,0.06),0_2px_8px_rgba(0,0,0,0.03)]">
                    <div class="w-[52px] h-[52px] rounded-full bg-indigo-500/[0.08] text-indigo-500 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Completely Free</h3>
                    <p class="text-sm text-gray-500">No hidden fees, no watermarks, no premium plans. Free forever.</p>
                </div>

                <div class="text-center p-7 rounded-[20px] transition-all duration-300 hover:bg-white hover:shadow-[0_0_30px_rgba(99,102,241,0.06),0_2px_8px_rgba(0,0,0,0.03)]">
                    <div class="w-[52px] h-[52px] rounded-full bg-violet-400/[0.08] text-violet-400 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">AI Background Removal</h3>
                    <p class="text-sm text-gray-500">Advanced neural network removes any background and replaces it with clean white.</p>
                </div>

                <div class="text-center p-7 rounded-[20px] transition-all duration-300 hover:bg-white hover:shadow-[0_0_30px_rgba(99,102,241,0.06),0_2px_8px_rgba(0,0,0,0.03)]">
                    <div class="w-[52px] h-[52px] rounded-full bg-pink-500/[0.08] text-pink-500 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Multiple Countries</h3>
                    <p class="text-sm text-gray-500">Support for passport and visa formats from dozens of countries worldwide.</p>
                </div>

                <div class="text-center p-7 rounded-[20px] transition-all duration-300 hover:bg-white hover:shadow-[0_0_30px_rgba(99,102,241,0.06),0_2px_8px_rgba(0,0,0,0.03)]">
                    <div class="w-[52px] h-[52px] rounded-full bg-emerald-500/[0.08] text-emerald-500 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2">Privacy First</h3>
                    <p class="text-sm text-gray-500">Photos are auto-deleted after processing. No accounts, no tracking.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- BOTTOM CTA --}}
    <section class="relative z-10 py-20">
        <div class="max-w-3xl mx-auto px-4">
            <div class="bg-white rounded-3xl p-14 text-center border border-indigo-500/[0.1] shadow-[0_0_60px_rgba(99,102,241,0.06),0_4px_24px_rgba(0,0,0,0.03)]">
                <h2 class="text-3xl sm:text-4xl font-extrabold gradient-text mb-4">Ready to Get Your Document Photo?</h2>
                <p class="text-gray-400 text-lg mb-8">No signup, no payment. Just upload and download.</p>
                <a href="#"
                   onclick="window.scrollTo({top: 0, behavior: 'smooth'}); return false;"
                   class="inline-flex items-center gap-2 gradient-bg text-white px-8 py-4 rounded-[14px] text-lg font-bold
                          shadow-[0_4px_20px_rgba(99,102,241,0.3)] hover:shadow-[0_8px_30px_rgba(99,102,241,0.4)]
                          transition-all duration-300 transform hover:-translate-y-0.5">
                    Get Started Now
                </a>
            </div>
        </div>
    </section>
@endsection
