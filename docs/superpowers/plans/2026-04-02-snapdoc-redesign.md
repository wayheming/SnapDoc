# SnapDoc Redesign — Light Peeky Theme Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Redesign the photo-processor app as "SnapDoc" with a light Peeky-inspired theme — grid background, gradient orbs, animated gradient text, glow cards, and navigation bar.

**Architecture:** Pure frontend redesign across 3 Blade templates. Replace the gradient hero with a unified light design. Global visual effects (grid, orbs) live in the layout. Section-specific markup lives in home.blade.php. Form card in the Livewire component gets minor styling tweaks.

**Tech Stack:** Laravel Blade, Tailwind CSS (CDN), custom CSS in `<style>` block

---

## File Map

| File | Action | Responsibility |
|------|--------|---------------|
| `app/.env` | Modify | APP_NAME → SnapDoc |
| `app/resources/views/layouts/app.blade.php` | Modify | Global styles, grid bg, orbs, nav bar, footer, title |
| `app/resources/views/home.blade.php` | Modify | Hero section, How It Works, Features, CTA |
| `app/resources/views/livewire/photo-processor.blade.php` | Modify | Form card glow styling |

---

### Task 1: Update APP_NAME and layout global styles

**Files:**
- Modify: `app/.env:1`
- Modify: `app/resources/views/layouts/app.blade.php`

- [ ] **Step 1: Update APP_NAME in .env**

Change line 1 of `app/.env`:
```
APP_NAME="SnapDoc"
```

- [ ] **Step 2: Replace the full `<style>` block in app.blade.php**

Replace the entire `<style>...</style>` block (lines 25-78) with:

```css
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
```

- [ ] **Step 3: Update `<title>` tag**

Change line 7:
```html
<title>{{ config('app.name', 'SnapDoc') }} — Free Passport & Visa Photo Maker</title>
```

- [ ] **Step 4: Replace `<body>` opening and add grid/orbs/nav before `@yield('content')`**

Replace the `<body>` tag and everything before `@yield('content')` (lines 81-82) with:

```html
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
```

- [ ] **Step 5: Replace the footer**

Replace the entire `<footer>...</footer>` block (lines 84-89) with:

```html
    <footer class="relative z-10 max-w-6xl mx-auto px-4 py-8 flex items-center justify-between border-t border-indigo-500/[0.08]">
        <p class="text-sm text-gray-400">&copy; {{ date('Y') }} {{ config('app.name', 'SnapDoc') }}. All rights reserved.</p>
        <a href="{{ route('privacy-policy') }}" class="text-sm text-gray-500 hover:text-indigo-500 transition">Privacy Policy</a>
    </footer>
```

- [ ] **Step 6: Verify layout renders**

Run: open the app in browser or `curl -s http://localhost:8080 | head -50`

Expected: page loads with grid background, floating orbs, "SnapDoc" gradient logo in nav, light footer.

- [ ] **Step 7: Commit**

```bash
git add app/.env app/resources/views/layouts/app.blade.php
git commit -m "feat: redesign layout — SnapDoc branding, grid bg, orbs, nav bar, light footer"
```

---

### Task 2: Redesign hero and content sections in home.blade.php

**Files:**
- Modify: `app/resources/views/home.blade.php`

- [ ] **Step 1: Replace the entire hero section**

Replace the hero `<section>` (lines 4-61, from `{{-- HERO SECTION` to the closing `</section>`) with:

```html
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
```

- [ ] **Step 2: Update How It Works section**

Replace the How It Works `<section>` (lines 63-108) with:

```html
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
```

- [ ] **Step 3: Update Features section**

Replace the Features `<section>` (lines 111-161) with:

```html
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
```

- [ ] **Step 4: Update Bottom CTA section**

Replace the CTA `<section>` (lines 163-175) with:

```html
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
```

- [ ] **Step 5: Verify all sections render**

Open the app in browser. Expected: light bg with grid + orbs visible behind all sections, no purple gradient hero, no wave divider, no dark footer. Step 1 shows updated text about light background.

- [ ] **Step 6: Commit**

```bash
git add app/resources/views/home.blade.php
git commit -m "feat: redesign hero, how-it-works, features, CTA — light peeky theme"
```

---

### Task 3: Update form card styling in Livewire component

**Files:**
- Modify: `app/resources/views/livewire/photo-processor.blade.php`

- [ ] **Step 1: Update the upload zone dashed border color**

In `photo-processor.blade.php`, change the upload zone border from `border-gray-200` to `border-indigo-500/[0.25]` and hover from `hover:border-indigo-400` to `hover:border-indigo-500`:

Find (line 26):
```html
<div class="relative border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-indigo-400 transition-colors">
```
Replace with:
```html
<div class="relative border-2 border-dashed border-indigo-500/[0.25] rounded-xl p-6 text-center hover:border-indigo-500 transition-colors">
```

- [ ] **Step 2: Update the photo preview border**

Find (line 14):
```html
<div class="relative rounded-xl overflow-hidden border border-indigo-200 bg-gray-50 mb-3">
```
Replace with:
```html
<div class="relative rounded-xl overflow-hidden border border-indigo-500/[0.15] bg-gray-50 mb-3">
```

- [ ] **Step 3: Update the select dropdown styling**

Find (line 56):
```html
<select wire:model="documentFormatId"
        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50 hover:bg-white transition">
```
Replace with:
```html
<select wire:model="documentFormatId"
        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-[#f9fafb] hover:bg-white transition">
```

- [ ] **Step 4: Update the submit button to use shadow glow**

Find (lines 85-86):
```html
<button type="submit"
        class="w-full gradient-bg text-white py-4 rounded-xl font-semibold text-lg
               hover:opacity-90 hover:shadow-lg hover:shadow-indigo-500/25 transition-all duration-300 transform hover:-translate-y-0.5">
```
Replace with:
```html
<button type="submit"
        class="w-full gradient-bg text-white py-4 rounded-[14px] font-bold text-lg
               shadow-[0_4px_16px_rgba(99,102,241,0.3)] hover:shadow-[0_8px_24px_rgba(99,102,241,0.4)]
               transition-all duration-300 transform hover:-translate-y-0.5">
```

- [ ] **Step 5: Update the download button styling**

Find (lines 146-147):
```html
<a href="{{ route('download', $order->uuid) }}"
   class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-green-600 text-white px-8 py-4 rounded-xl font-semibold text-lg
          hover:from-emerald-600 hover:to-green-700 hover:shadow-lg hover:shadow-green-500/25 transition-all duration-300 transform hover:-translate-y-0.5">
```
Replace with:
```html
<a href="{{ route('download', $order->uuid) }}"
   class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-green-600 text-white px-8 py-4 rounded-[14px] font-bold text-lg
          hover:from-emerald-600 hover:to-green-700 shadow-[0_4px_16px_rgba(16,185,129,0.3)] hover:shadow-[0_8px_24px_rgba(16,185,129,0.4)]
          transition-all duration-300 transform hover:-translate-y-0.5">
```

- [ ] **Step 6: Verify form renders correctly**

Open app, check upload form card has indigo glow border, upload zone has indigo dashed border, button has indigo glow shadow.

- [ ] **Step 7: Commit**

```bash
git add app/resources/views/livewire/photo-processor.blade.php
git commit -m "feat: update form card styling — indigo glow borders and shadows"
```
