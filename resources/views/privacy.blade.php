<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Privacy Policy - {{ config('app.name', 'Rendee') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        <script>
            (function() {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        @vite(['resources/css/app.css'])

        <style>
            /* Custom smooth micro-interactions & gradient animation */
            @keyframes pulseGlow {
                0%, 100% { opacity: 0.45; transform: scale(1); }
                50% { opacity: 0.75; transform: scale(1.08); }
            }
            .glow-mesh-1 {
                animation: pulseGlow 12s ease-in-out infinite alternate;
            }
            .glow-mesh-2 {
                animation: pulseGlow 16s ease-in-out infinite alternate-reverse;
            }
            .glass-panel {
                background: rgba(255, 255, 255, 0.72);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
            }
            .dark .glass-panel {
                background: rgba(18, 18, 20, 0.75);
            }
            .glass-card {
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.85) 0%, rgba(255, 255, 255, 0.5) 100%);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
            }
            .dark .glass-card {
                background: linear-gradient(135deg, rgba(24, 24, 27, 0.85) 0%, rgba(18, 18, 20, 0.5) 100%);
            }
            .toc-link.active {
                background-color: rgba(16, 185, 129, 0.1);
                color: rgb(5, 150, 105);
                font-weight: 600;
                border-left: 2px solid rgb(16, 185, 129);
                padding-left: 0.75rem;
            }
            .dark .toc-link.active {
                background-color: rgba(16, 185, 129, 0.15);
                color: rgb(52, 211, 153);
                border-left-color: rgb(52, 211, 153);
            }
        </style>
    </head>
    <body class="relative min-h-screen bg-[#FAFAF9] font-sans text-[#1c1917] antialiased selection:bg-emerald-500 selection:text-white dark:bg-[#09090b] dark:text-[#f4f4f5]">

        {{-- Reading Progress Bar --}}
        <div id="scroll-progress" class="fixed top-0 left-0 z-50 h-[3px] w-0 bg-gradient-to-r from-emerald-500 via-teal-400 to-blue-500 transition-all duration-75"></div>

        {{-- Dynamic Ambient Background Glows --}}
        <div class="pointer-events-none fixed inset-0 z-0 overflow-hidden">
            <div class="glow-mesh-1 absolute -top-40 left-1/2 h-[580px] w-[800px] -translate-x-1/2 rounded-full bg-gradient-to-b from-emerald-400/20 via-teal-500/10 to-transparent blur-3xl dark:from-emerald-500/15 dark:via-teal-600/10"></div>
            <div class="glow-mesh-2 absolute top-[450px] -right-32 h-[500px] w-[500px] rounded-full bg-gradient-to-tr from-blue-500/15 via-indigo-500/10 to-transparent blur-3xl dark:from-blue-600/12 dark:via-indigo-600/10"></div>
            <div class="absolute bottom-20 -left-32 h-[450px] w-[450px] rounded-full bg-gradient-to-br from-emerald-500/10 to-transparent blur-3xl dark:from-emerald-600/10"></div>
            {{-- Subtle geometric dot pattern overlay --}}
            <div class="absolute inset-0 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:24px_24px] opacity-60 dark:bg-[radial-gradient(#27272a_1px,transparent_1px)] dark:opacity-40"></div>
        </div>

        {{-- Sticky Navigation Header --}}
        <header class="sticky top-0 z-40 border-b border-black/[0.06] bg-[#FAFAF9]/80 backdrop-blur-md transition-colors dark:border-white/[0.08] dark:bg-[#09090b]/80">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <a href="{{ route('home') }}" class="group flex items-center gap-3 transition">
                    <span class="relative flex size-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-lg font-bold text-white shadow-md shadow-emerald-600/20 ring-1 ring-black/10 transition-all duration-300 group-hover:scale-105 group-hover:shadow-emerald-600/35 dark:ring-white/20">
                        R
                    </span>
                    <div class="flex flex-col">
                        <span class="text-base font-bold tracking-tight text-[#1c1917] transition group-hover:text-emerald-600 dark:text-[#f4f4f5] dark:group-hover:text-emerald-400">Rendee</span>
                        <span class="text-[10px] font-medium uppercase tracking-widest text-[#78716c] dark:text-[#a1a1aa]">Healthcare Platform</span>
                    </div>
                </a>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        onclick="document.documentElement.classList.toggle('dark')"
                        aria-label="Toggle theme"
                        class="inline-flex size-9 items-center justify-center rounded-xl border border-black/[0.08] bg-white/70 text-[#57534e] shadow-sm transition hover:bg-black/[0.04] hover:text-[#1c1917] dark:border-white/[0.12] dark:bg-[#18181b]/70 dark:text-[#a1a1aa] dark:hover:bg-white/[0.06] dark:hover:text-[#f4f4f5]"
                    >
                        <!-- Sun icon -->
                        <svg class="hidden size-4 dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <!-- Moon icon -->
                        <svg class="size-4 dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <a href="{{ route('home') }}" class="group inline-flex items-center gap-1.5 rounded-xl border border-black/[0.08] bg-white/80 px-4 py-2 text-xs font-semibold text-[#1c1917] shadow-sm transition-all hover:border-black/20 hover:bg-white hover:shadow dark:border-white/[0.12] dark:bg-[#18181b]/80 dark:text-[#f4f4f5] dark:hover:border-white/20 dark:hover:bg-[#202024]">
                        <svg class="size-3.5 transition-transform duration-200 group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span>Back to Home</span>
                    </a>
                </div>
            </div>
        </header>

        {{-- Hero Header --}}
        <div class="relative z-10 border-b border-black/[0.06] py-16 sm:py-24 dark:border-white/[0.08]">
            <div class="mx-auto max-w-6xl px-6">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3.5 py-1 text-xs font-semibold text-emerald-700 shadow-sm backdrop-blur-md dark:border-emerald-400/30 dark:bg-emerald-500/15 dark:text-emerald-300">
                        <span class="relative flex size-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                        </span>
                        Privacy & Data Protection Notice
                    </div>

                    <h1 class="mt-5 text-4xl font-extrabold tracking-tight text-[#1c1917] sm:text-5xl lg:text-6xl dark:text-[#f4f4f5]">
                        Privacy Policy
                    </h1>
                    <p class="mt-4 text-base leading-relaxed text-[#57534e] sm:text-lg dark:text-[#a1a1aa]">
                        At <strong class="font-semibold text-[#1c1917] dark:text-[#f4f4f5]">Rendee</strong>, we are committed to safeguarding your privacy and ensuring maximum security for your personal and healthcare scheduling data.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-4 text-xs font-medium text-[#78716c] dark:text-[#a1a1aa]">
                        <div class="flex items-center gap-2 rounded-lg border border-black/[0.06] bg-white/60 px-3 py-1.5 backdrop-blur-sm dark:border-white/[0.08] dark:bg-white/[0.03]">
                            <svg class="size-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Effective Date: <strong>{{ date('F d, Y') }}</strong></span>
                        </div>
                        <div class="flex items-center gap-2 rounded-lg border border-black/[0.06] bg-white/60 px-3 py-1.5 backdrop-blur-sm dark:border-white/[0.08] dark:bg-white/[0.03]">
                            <svg class="size-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            <span>Enterprise Encryption & TLS</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Layout with Sticky Sidebar --}}
        <main class="relative z-10 mx-auto max-w-6xl px-6 py-12 lg:py-16">
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-12">
                {{-- Sidebar Navigation (Desktop) --}}
                <aside class="hidden lg:col-span-4 lg:block">
                    <div class="glass-panel sticky top-24 space-y-6 rounded-2xl border border-black/[0.08] p-6 shadow-sm dark:border-white/[0.08]">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-[#78716c] dark:text-[#a1a1aa]">
                                Table of Contents
                            </h3>
                            <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">8 Sections</span>
                        </div>

                        <nav id="toc-nav" class="space-y-1 text-sm font-medium">
                            <a href="#information-we-collect" class="toc-link block rounded-lg px-3 py-2 text-[#57534e] transition-all hover:bg-black/[0.04] hover:text-[#1c1917] dark:text-[#a1a1aa] dark:hover:bg-white/[0.04] dark:hover:text-[#f4f4f5]">
                                1. Information We Collect
                            </a>
                            <a href="#how-we-use-information" class="toc-link block rounded-lg px-3 py-2 text-[#57534e] transition-all hover:bg-black/[0.04] hover:text-[#1c1917] dark:text-[#a1a1aa] dark:hover:bg-white/[0.04] dark:hover:text-[#f4f4f5]">
                                2. How We Use Information
                            </a>
                            <a href="#sharing-and-disclosure" class="toc-link block rounded-lg px-3 py-2 text-[#57534e] transition-all hover:bg-black/[0.04] hover:text-[#1c1917] dark:text-[#a1a1aa] dark:hover:bg-white/[0.04] dark:hover:text-[#f4f4f5]">
                                3. Information Sharing
                            </a>
                            <a href="#security-and-retention" class="toc-link block rounded-lg px-3 py-2 text-[#57534e] transition-all hover:bg-black/[0.04] hover:text-[#1c1917] dark:text-[#a1a1aa] dark:hover:bg-white/[0.04] dark:hover:text-[#f4f4f5]">
                                4. Security & Data Retention
                            </a>
                            <a href="#your-rights" class="toc-link block rounded-lg px-3 py-2 text-[#57534e] transition-all hover:bg-black/[0.04] hover:text-[#1c1917] dark:text-[#a1a1aa] dark:hover:bg-white/[0.04] dark:hover:text-[#f4f4f5]">
                                5. Your Rights and Choices
                            </a>
                            <a href="#children-privacy" class="toc-link block rounded-lg px-3 py-2 text-[#57534e] transition-all hover:bg-black/[0.04] hover:text-[#1c1917] dark:text-[#a1a1aa] dark:hover:bg-white/[0.04] dark:hover:text-[#f4f4f5]">
                                6. Children's Privacy
                            </a>
                            <a href="#policy-updates" class="toc-link block rounded-lg px-3 py-2 text-[#57534e] transition-all hover:bg-black/[0.04] hover:text-[#1c1917] dark:text-[#a1a1aa] dark:hover:bg-white/[0.04] dark:hover:text-[#f4f4f5]">
                                7. Policy Updates
                            </a>
                            <a href="#contact-us" class="toc-link block rounded-lg px-3 py-2 text-[#57534e] transition-all hover:bg-black/[0.04] hover:text-[#1c1917] dark:text-[#a1a1aa] dark:hover:bg-white/[0.04] dark:hover:text-[#f4f4f5]">
                                8. Contact Information
                            </a>
                        </nav>

                        <div class="border-t border-black/[0.06] pt-5 dark:border-white/[0.08]">
                            <p class="text-xs leading-relaxed text-[#78716c] dark:text-[#a1a1aa]">
                                Need help or have inquiries regarding your personal data?
                            </p>
                            <a href="mailto:privacy@rendee.app" class="mt-2.5 inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 transition hover:text-emerald-700 hover:underline dark:text-emerald-400 dark:hover:text-emerald-300">
                                <span>Email privacy team</span>
                                <svg class="size-3 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </aside>

                {{-- Policy Content Articles --}}
                <div class="space-y-16 lg:col-span-8">
                    {{-- Section 1 --}}
                    <section id="information-we-collect" class="scroll-mt-28 space-y-6">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 text-xs font-bold text-emerald-700 ring-1 ring-emerald-500/30 dark:text-emerald-300">01</span>
                            <h2 class="text-2xl font-bold tracking-tight text-[#1c1917] sm:text-3xl dark:text-[#f4f4f5]">
                                Information We Collect
                            </h2>
                        </div>
                        <p class="leading-relaxed text-[#57534e] sm:text-base dark:text-[#d4d4d8]">
                            To provide, maintain, and personalize our healthcare scheduling platform for both patients and healthcare partners (professionals, medical clinics, and pharmacies), we collect the following types of information:
                        </p>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="glass-card group rounded-2xl border border-black/[0.06] p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-emerald-500/30 hover:shadow-md dark:border-white/[0.08]">
                                <div class="mb-3 flex size-9 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 transition group-hover:bg-emerald-500 group-hover:text-white dark:bg-emerald-500/20 dark:text-emerald-400">
                                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-[#1c1917] dark:text-[#f4f4f5]">Account Information</h4>
                                <p class="mt-1.5 text-xs leading-relaxed text-[#78716c] dark:text-[#a1a1aa]">
                                    Name, email address, telephone number, authentication credentials, avatar photos, and user role profile.
                                </p>
                            </div>

                            <div class="glass-card group rounded-2xl border border-black/[0.06] p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-emerald-500/30 hover:shadow-md dark:border-white/[0.08]">
                                <div class="mb-3 flex size-9 items-center justify-center rounded-xl bg-blue-500/10 text-blue-600 transition group-hover:bg-blue-500 group-hover:text-white dark:bg-blue-500/20 dark:text-blue-400">
                                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-[#1c1917] dark:text-[#f4f4f5]">Healthcare Partner Data</h4>
                                <p class="mt-1.5 text-xs leading-relaxed text-[#78716c] dark:text-[#a1a1aa]">
                                    Professional licenses, medical specialties, service catalogs, clinic locations, operating hours, and consultation agendas.
                                </p>
                            </div>

                            <div class="glass-card group rounded-2xl border border-black/[0.06] p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-emerald-500/30 hover:shadow-md dark:border-white/[0.08]">
                                <div class="mb-3 flex size-9 items-center justify-center rounded-xl bg-purple-500/10 text-purple-600 transition group-hover:bg-purple-500 group-hover:text-white dark:bg-purple-500/20 dark:text-purple-400">
                                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-[#1c1917] dark:text-[#f4f4f5]">Bookings & Consultation Records</h4>
                                <p class="mt-1.5 text-xs leading-relaxed text-[#78716c] dark:text-[#a1a1aa]">
                                    Appointment requests, booking suggestions, status updates, consultation confirmations, and partner feedback.
                                </p>
                            </div>

                            <div class="glass-card group rounded-2xl border border-black/[0.06] p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-emerald-500/30 hover:shadow-md dark:border-white/[0.08]">
                                <div class="mb-3 flex size-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 transition group-hover:bg-amber-500 group-hover:text-white dark:bg-amber-500/20 dark:text-amber-400">
                                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-[#1c1917] dark:text-[#f4f4f5]">Location & Device Data</h4>
                                <p class="mt-1.5 text-xs leading-relaxed text-[#78716c] dark:text-[#a1a1aa]">
                                    Nearby geo-coordinates (when searching clinics/providers), push notification tokens, IP address, and platform metrics.
                                </p>
                            </div>
                        </div>

                        {{-- Prominent Health Data Callout Box --}}
                        <div class="relative overflow-hidden rounded-2xl border border-emerald-500/30 bg-gradient-to-r from-emerald-500/10 via-teal-500/5 to-transparent p-5 sm:p-6 dark:border-emerald-400/30 dark:from-emerald-500/15 dark:via-teal-500/10">
                            <div class="flex items-start gap-4">
                                <div class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-700 ring-1 ring-emerald-500/30 dark:bg-emerald-400/20 dark:text-emerald-300">
                                    <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                </div>
                                <div class="space-y-1.5">
                                    <h4 class="text-sm font-bold text-[#1c1917] sm:text-base dark:text-[#f4f4f5]">
                                        No Native Health Framework Sync (Apple Health / Health Connect)
                                    </h4>
                                    <p class="text-xs leading-relaxed text-[#57534e] sm:text-sm dark:text-[#d4d4d8]">
                                        Rendee <strong>does not access, sync, or collect background biometric records from Apple HealthKit or Google Health Connect / Google Fit</strong>. We only process health-related details that you <strong>manually and explicitly enter</strong> directly within the Rendee application (such as booking notes, selected medical services, or consultation specialties).
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Section 2 --}}
                    <section id="how-we-use-information" class="scroll-mt-28 space-y-6">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 text-xs font-bold text-emerald-700 ring-1 ring-emerald-500/30 dark:text-emerald-300">02</span>
                            <h2 class="text-2xl font-bold tracking-tight text-[#1c1917] sm:text-3xl dark:text-[#f4f4f5]">
                                How We Use Information
                            </h2>
                        </div>
                        <p class="leading-relaxed text-[#57534e] sm:text-base dark:text-[#d4d4d8]">
                            We utilize the gathered information exclusively for transparent and legitimate purposes:
                        </p>
                        <div class="grid gap-3">
                            <div class="flex items-start gap-3.5 rounded-xl border border-black/[0.05] bg-white/50 p-4 shadow-sm backdrop-blur-sm transition-all hover:bg-white/80 dark:border-white/[0.06] dark:bg-[#18181b]/50 dark:hover:bg-[#18181b]/80">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-xs font-bold text-emerald-600 dark:bg-emerald-500/25 dark:text-emerald-400">
                                    ✓
                                </span>
                                <div>
                                    <strong class="text-sm font-semibold text-[#1c1917] dark:text-[#f4f4f5]">Facilitating Healthcare Connections:</strong>
                                    <p class="mt-0.5 text-xs leading-relaxed text-[#78716c] dark:text-[#a1a1aa]">Connecting patients with verified medical specialists, clinics, and pharmacies with zero friction.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3.5 rounded-xl border border-black/[0.05] bg-white/50 p-4 shadow-sm backdrop-blur-sm transition-all hover:bg-white/80 dark:border-white/[0.06] dark:bg-[#18181b]/50 dark:hover:bg-[#18181b]/80">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-xs font-bold text-emerald-600 dark:bg-emerald-500/25 dark:text-emerald-400">
                                    ✓
                                </span>
                                <div>
                                    <strong class="text-sm font-semibold text-[#1c1917] dark:text-[#f4f4f5]">Managing Catalogs & Schedules:</strong>
                                    <p class="mt-0.5 text-xs leading-relaxed text-[#78716c] dark:text-[#a1a1aa]">Powering live availability, time-slot booking agendas, and proposal confirmations in real time.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3.5 rounded-xl border border-black/[0.05] bg-white/50 p-4 shadow-sm backdrop-blur-sm transition-all hover:bg-white/80 dark:border-white/[0.06] dark:bg-[#18181b]/50 dark:hover:bg-[#18181b]/80">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-xs font-bold text-emerald-600 dark:bg-emerald-500/25 dark:text-emerald-400">
                                    ✓
                                </span>
                                <div>
                                    <strong class="text-sm font-semibold text-[#1c1917] dark:text-[#f4f4f5]">Real-time Notifications:</strong>
                                    <p class="mt-0.5 text-xs leading-relaxed text-[#78716c] dark:text-[#a1a1aa]">Sending status updates, reminders, rescheduling suggestions, and system alerts reliably.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3.5 rounded-xl border border-black/[0.05] bg-white/50 p-4 shadow-sm backdrop-blur-sm transition-all hover:bg-white/80 dark:border-white/[0.06] dark:bg-[#18181b]/50 dark:hover:bg-[#18181b]/80">
                                <span class="mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-xs font-bold text-emerald-600 dark:bg-emerald-500/25 dark:text-emerald-400">
                                    ✓
                                </span>
                                <div>
                                    <strong class="text-sm font-semibold text-[#1c1917] dark:text-[#f4f4f5]">Trust & Security:</strong>
                                    <p class="mt-0.5 text-xs leading-relaxed text-[#78716c] dark:text-[#a1a1aa]">Preventing unauthorized access or fraudulent activity, verifying partner credentials, and ensuring platform integrity.</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Section 3 --}}
                    <section id="sharing-and-disclosure" class="scroll-mt-28 space-y-6">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 text-xs font-bold text-emerald-700 ring-1 ring-emerald-500/30 dark:text-emerald-300">03</span>
                            <h2 class="text-2xl font-bold tracking-tight text-[#1c1917] sm:text-3xl dark:text-[#f4f4f5]">
                                Information Sharing and Disclosure
                            </h2>
                        </div>
                        <p class="leading-relaxed text-[#57534e] sm:text-base dark:text-[#d4d4d8]">
                            We value your trust above all else. <strong class="font-bold text-[#1c1917] dark:text-[#f4f4f5]">We do not sell, rent, or trade your personal or health information with third-party advertisers.</strong>
                        </p>

                        <div class="glass-card rounded-2xl border border-black/[0.08] p-6 shadow-sm dark:border-white/[0.08]">
                            <h3 class="flex items-center gap-2 font-bold text-[#1c1917] dark:text-[#f4f4f5]">
                                <svg class="size-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>Permitted Disclosures:</span>
                            </h3>
                            <ul class="mt-4 space-y-3 text-sm leading-relaxed text-[#57534e] dark:text-[#a1a1aa]">
                                <li class="flex items-start gap-2.5">
                                    <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-emerald-500"></span>
                                    <span><strong>Direct Healthcare Providers:</strong> Information strictly necessary to confirm and fulfill requested appointments is transmitted to your chosen practitioner or clinic.</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-emerald-500"></span>
                                    <span><strong>Cloud Infrastructure:</strong> Certified, enterprise-grade cloud providers and database systems bound by rigorous Data Processing Agreements (DPA).</span>
                                </li>
                                <li class="flex items-start gap-2.5">
                                    <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-emerald-500"></span>
                                    <span><strong>Legal Obligations:</strong> In accordance with health data compliance requirements, valid judicial warrants, or lawful governmental inquiries.</span>
                                </li>
                            </ul>
                        </div>
                    </section>

                    {{-- Section 4 --}}
                    <section id="security-and-retention" class="scroll-mt-28 space-y-6">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 text-xs font-bold text-emerald-700 ring-1 ring-emerald-500/30 dark:text-emerald-300">04</span>
                            <h2 class="text-2xl font-bold tracking-tight text-[#1c1917] sm:text-3xl dark:text-[#f4f4f5]">
                                Security & Data Retention
                            </h2>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="glass-card rounded-2xl border border-black/[0.06] p-5 dark:border-white/[0.08]">
                                <div class="mb-2.5 flex size-8 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                                    <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-sm text-[#1c1917] dark:text-[#f4f4f5]">Encryption & Protocols</h4>
                                <p class="mt-1 text-xs leading-relaxed text-[#78716c] dark:text-[#a1a1aa]">
                                    All data in transit is encrypted using modern TLS/HTTPS. Rest-state storage implements strict role-based access control (RBAC).
                                </p>
                            </div>

                            <div class="glass-card rounded-2xl border border-black/[0.06] p-5 dark:border-white/[0.08]">
                                <div class="mb-2.5 flex size-8 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                                    <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </div>
                                <h4 class="font-bold text-sm text-[#1c1917] dark:text-[#f4f4f5]">Retention Schedule</h4>
                                <p class="mt-1 text-xs leading-relaxed text-[#78716c] dark:text-[#a1a1aa]">
                                    We retain records solely as necessary to sustain services, fulfill legal/medical audit mandates, or until you initiate account erasure.
                                </p>
                            </div>
                        </div>
                    </section>

                    {{-- Section 5 --}}
                    <section id="your-rights" class="scroll-mt-28 space-y-6">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex size-8 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500/20 to-teal-500/20 text-xs font-bold text-emerald-700 ring-1 ring-emerald-500/30 dark:text-emerald-300">05</span>
                            <h2 class="text-2xl font-bold tracking-tight text-[#1c1917] sm:text-3xl dark:text-[#f4f4f5]">
                                Your Rights and Choices
                            </h2>
                        </div>
                        <p class="leading-relaxed text-[#57534e] sm:text-base dark:text-[#d4d4d8]">
                            You have statutory control over your personal information:
                        </p>
                        <div class="grid gap-3.5 sm:grid-cols-2">
                            <div class="glass-card rounded-xl border border-black/[0.06] p-4.5 transition-all hover:border-emerald-500/30 dark:border-white/[0.08]">
                                <h4 class="font-bold text-sm text-[#1c1917] dark:text-[#f4f4f5]">Access & Portability</h4>
                                <p class="mt-1 text-xs text-[#78716c] dark:text-[#a1a1aa]">View, review, and request an export of your personal data profile.</p>
                            </div>
                            <div class="glass-card rounded-xl border border-black/[0.06] p-4.5 transition-all hover:border-emerald-500/30 dark:border-white/[0.08]">
                                <h4 class="font-bold text-sm text-[#1c1917] dark:text-[#f4f4f5]">Rectification</h4>
                                <p class="mt-1 text-xs text-[#78716c] dark:text-[#a1a1aa]">Update inaccurate or outdated contact and account credentials in real time.</p>
                            </div>
                            <a href="{{ route('request-account-deletion') }}" class="glass-card block rounded-xl border border-rose-500/20 p-4.5 transition-all hover:border-rose-500/50 hover:shadow-sm dark:border-rose-500/30">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-bold text-sm text-rose-600 dark:text-rose-400">Erasure (Right to be Forgotten) &rarr;</h4>
                                    <span class="rounded-md bg-rose-500/10 px-2 py-0.5 text-[10px] font-semibold text-rose-600 dark:text-rose-400">Request Deletion</span>
                                </div>
                                <p class="mt-1 text-xs text-[#78716c] dark:text-[#a1a1aa]">Request complete deletion of your account and personal history permanently via our dedicated portal.</p>
                            </a>
                            <div class="glass-card rounded-xl border border-black/[0.06] p-4.5 transition-all hover:border-emerald-500/30 dark:border-white/[0.08]">
                                <h4 class="font-bold text-sm text-[#1c1917] dark:text-[#f4f4f5]">Notification Preferences</h4>
                                <p class="mt-1 text-xs text-[#78716c] dark:text-[#a1a1aa]">Manage or opt out of non-critical push notifications and email communications.</p>
                            </div>
                        </div>
                    </section>

                    {{-- Section 6 & 7 --}}
                    <div class="grid gap-6 sm:grid-cols-2">
                        <section id="children-privacy" class="glass-card scroll-mt-28 space-y-3 rounded-2xl border border-black/[0.06] p-6 shadow-sm dark:border-white/[0.08]">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex size-7 items-center justify-center rounded-lg bg-emerald-500/10 text-xs font-bold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">06</span>
                                <h3 class="font-bold text-[#1c1917] dark:text-[#f4f4f5]">Children's Privacy</h3>
                            </div>
                            <p class="text-xs leading-relaxed text-[#57534e] sm:text-sm dark:text-[#a1a1aa]">
                                Rendee services are not directed to individuals under the age of 18 without parental or guardian authorization. We do not knowingly collect personal data from minors.
                            </p>
                        </section>

                        <section id="policy-updates" class="glass-card scroll-mt-28 space-y-3 rounded-2xl border border-black/[0.06] p-6 shadow-sm dark:border-white/[0.08]">
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex size-7 items-center justify-center rounded-lg bg-emerald-500/10 text-xs font-bold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">07</span>
                                <h3 class="font-bold text-[#1c1917] dark:text-[#f4f4f5]">Policy Updates</h3>
                            </div>
                            <p class="text-xs leading-relaxed text-[#57534e] sm:text-sm dark:text-[#a1a1aa]">
                                We may periodically update this policy. When critical revisions occur, we will post notice on our platform or provide direct in-app announcements.
                            </p>
                        </section>
                    </div>

                    {{-- Section 8: Contact Card --}}
                    <section id="contact-us" class="relative scroll-mt-28 overflow-hidden rounded-3xl border border-black/[0.08] bg-gradient-to-br from-emerald-500/10 via-teal-500/5 to-transparent p-7 sm:p-10 shadow-sm dark:border-white/[0.08] dark:from-emerald-500/15 dark:via-teal-500/10">
                        <div class="relative z-10 flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                            <div class="space-y-2">
                                <div class="inline-flex items-center gap-1.5 rounded-md bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                                    Section 08 • Inquiries
                                </div>
                                <h3 class="text-2xl font-bold tracking-tight text-[#1c1917] sm:text-3xl dark:text-[#f4f4f5]">
                                    Have Questions or Requests?
                                </h3>
                                <p class="max-w-md text-sm leading-relaxed text-[#57534e] dark:text-[#a1a1aa]">
                                    Our dedicated privacy officer and legal compliance team are here to assist with any questions regarding your health data privacy rights.
                                </p>
                            </div>

                            <div class="shrink-0">
                                <a href="mailto:privacy@rendee.app" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/20 transition-all hover:scale-105 hover:from-emerald-500 hover:to-teal-500 hover:shadow-emerald-600/30 active:scale-95">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <span>Contact Privacy Team</span>
                                </a>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </main>

        {{-- Footer --}}
        <footer class="relative z-10 border-t border-black/[0.06] bg-[#FAFAF9]/80 py-10 text-xs text-[#78716c] backdrop-blur-md dark:border-white/[0.08] dark:bg-[#09090b]/80 dark:text-[#a1a1aa]">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-6 px-6 sm:flex-row">
                <div class="flex items-center gap-3">
                    <span class="inline-flex size-6 items-center justify-center rounded-lg bg-gradient-to-tr from-emerald-600 to-teal-500 text-[10px] font-bold text-white shadow-sm">
                        R
                    </span>
                    <span>&copy; {{ date('Y') }} Rendee Inc. All rights reserved.</span>
                </div>
                <div class="flex items-center gap-6 font-medium">
                    <a href="{{ route('privacy') }}" class="font-semibold text-emerald-600 underline underline-offset-4 dark:text-emerald-400">Privacy Policy</a>
                    <a href="{{ route('home') }}" class="transition hover:text-black dark:hover:text-white">Home</a>
                    <a href="mailto:support@rendee.app" class="transition hover:text-black dark:hover:text-white">Support</a>
                </div>
            </div>
        </footer>

        {{-- Scroll Progress & Active Table of Contents Highlighter Script --}}
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const progressBar = document.getElementById('scroll-progress');
                const tocLinks = document.querySelectorAll('.toc-link');
                const sections = document.querySelectorAll('main section[id]');

                window.addEventListener('scroll', () => {
                    const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
                    const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
                    const scrolled = (winScroll / height) * 100;
                    if (progressBar) {
                        progressBar.style.width = scrolled + '%';
                    }

                    // Highlight Active TOC Item
                    let currentSectionId = '';
                    sections.forEach(section => {
                        const sectionTop = section.offsetTop - 140;
                        if (winScroll >= sectionTop) {
                            currentSectionId = section.getAttribute('id');
                        }
                    });

                    tocLinks.forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === '#' + currentSectionId) {
                            link.classList.add('active');
                        }
                    });
                });
            });
        </script>
    </body>
</html>

