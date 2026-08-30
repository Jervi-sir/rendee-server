<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Request Account Deletion - {{ config('app.name', 'Rendee') }}</title>

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
                background: rgba(255, 255, 255, 0.75);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
            }
            .dark .glass-panel {
                background: rgba(18, 18, 20, 0.8);
            }
            .glass-card {
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.6) 100%);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
            }
            .dark .glass-card {
                background: linear-gradient(135deg, rgba(24, 24, 27, 0.9) 0%, rgba(18, 18, 20, 0.6) 100%);
            }
        </style>
    </head>
    <body class="relative min-h-screen bg-[#FAFAF9] font-sans text-[#1c1917] antialiased selection:bg-rose-500 selection:text-white dark:bg-[#09090b] dark:text-[#f4f4f5]">

        {{-- Dynamic Ambient Background Glows --}}
        <div class="pointer-events-none fixed inset-0 z-0 overflow-hidden">
            <div class="glow-mesh-1 absolute -top-40 left-1/2 h-[580px] w-[800px] -translate-x-1/2 rounded-full bg-gradient-to-b from-rose-500/15 via-amber-500/10 to-transparent blur-3xl dark:from-rose-600/15 dark:via-amber-600/10"></div>
            <div class="glow-mesh-2 absolute top-[450px] -right-32 h-[500px] w-[500px] rounded-full bg-gradient-to-tr from-emerald-500/10 via-teal-500/10 to-transparent blur-3xl dark:from-emerald-600/10 dark:via-teal-600/10"></div>
            <div class="absolute inset-0 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:24px_24px] opacity-60 dark:bg-[radial-gradient(#27272a_1px,transparent_1px)] dark:opacity-40"></div>
        </div>

        {{-- Sticky Navigation Header --}}
        <header class="sticky top-0 z-40 border-b border-black/[0.06] bg-[#FAFAF9]/80 backdrop-blur-md transition-colors dark:border-white/[0.08] dark:bg-[#09090b]/80">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
                <a href="{{ route('home') }}" class="group flex items-center gap-3 transition">
                    <span class="relative flex size-10 items-center justify-center rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-lg font-bold text-white shadow-md shadow-emerald-600/20 ring-1 ring-black/10 transition-all duration-300 group-hover:scale-105 group-hover:shadow-emerald-600/35 dark:ring-white/20">
                        R
                    </span>
                    <div class="flex flex-col">
                        <span class="text-base font-bold tracking-tight text-[#1c1917] transition group-hover:text-emerald-600 dark:text-[#f4f4f5] dark:group-hover:text-emerald-400">Rendee</span>
                        <span class="text-[10px] font-medium uppercase tracking-widest text-[#78716c] dark:text-[#a1a1aa]">Account Management</span>
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

                    <a href="{{ route('privacy') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-black/[0.08] bg-white/80 px-3.5 py-2 text-xs font-semibold text-[#1c1917] shadow-sm transition-all hover:border-black/20 hover:bg-white dark:border-white/[0.12] dark:bg-[#18181b]/80 dark:text-[#f4f4f5] dark:hover:border-white/20">
                        <span>Privacy Policy</span>
                    </a>

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
        <div class="relative z-10 border-b border-black/[0.06] py-14 sm:py-20 dark:border-white/[0.08]">
            <div class="mx-auto max-w-5xl px-6">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 rounded-full border border-rose-500/30 bg-rose-500/10 px-3.5 py-1 text-xs font-semibold text-rose-700 shadow-sm backdrop-blur-md dark:border-rose-400/30 dark:bg-rose-500/15 dark:text-rose-300">
                        <span class="size-2 rounded-full bg-rose-500"></span>
                        Data Privacy & Account Rights (GDPR / CCPA)
                    </div>

                    <h1 class="mt-5 text-4xl font-extrabold tracking-tight text-[#1c1917] sm:text-5xl lg:text-6xl dark:text-[#f4f4f5]">
                        Request Account Deletion
                    </h1>
                    <p class="mt-4 text-base leading-relaxed text-[#57534e] sm:text-lg dark:text-[#a1a1aa]">
                        We respect your right to be forgotten. You can request the permanent deletion of your Rendee account and all associated personal records at any time.
                    </p>
                </div>
            </div>
        </div>

        {{-- Main Container --}}
        <main class="relative z-10 mx-auto max-w-5xl px-6 py-12 lg:py-16">
            <div class="grid grid-cols-1 gap-12 lg:grid-cols-12">
                {{-- Left / Form Column --}}
                <div class="space-y-10 lg:col-span-7">
                    {{-- Step-by-step Request Form --}}
                    <div class="glass-panel overflow-hidden rounded-3xl border border-black/[0.08] p-6 shadow-sm sm:p-8 dark:border-white/[0.08]">
                        <div class="flex items-center gap-3">
                            <span class="flex size-8 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 dark:bg-rose-500/20 dark:text-rose-400">
                                <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </span>
                            <h2 class="text-xl font-bold tracking-tight text-[#1c1917] dark:text-[#f4f4f5]">
                                Submit Deletion Request
                            </h2>
                        </div>
                        <p class="mt-2 text-xs leading-relaxed text-[#78716c] dark:text-[#a1a1aa]">
                            Please fill out the form below or contact our security & compliance team directly. We will verify your identity to prevent unauthorized removal.
                        </p>

                        <form id="deletion-request-form" class="mt-6 space-y-4" onsubmit="event.preventDefault(); handleFormSubmit();">
                            <div>
                                <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-[#57534e] dark:text-[#a1a1aa]">
                                    Registered Email Address <span class="text-rose-500">*</span>
                                </label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    required
                                    placeholder="you@example.com"
                                    class="mt-1.5 w-full rounded-xl border border-black/[0.1] bg-white/80 px-4 py-2.5 text-sm text-[#1c1917] shadow-sm transition placeholder:text-[#a8a29e] focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-white/[0.12] dark:bg-[#18181b]/80 dark:text-[#f4f4f5] dark:placeholder:text-[#52525b]"
                                />
                            </div>

                            <div>
                                <label for="account-type" class="block text-xs font-semibold uppercase tracking-wider text-[#57534e] dark:text-[#a1a1aa]">
                                    Account Role / Type <span class="text-rose-500">*</span>
                                </label>
                                <select
                                    id="account-type"
                                    name="account_type"
                                    required
                                    class="mt-1.5 w-full rounded-xl border border-black/[0.1] bg-white/80 px-4 py-2.5 text-sm text-[#1c1917] shadow-sm transition focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-white/[0.12] dark:bg-[#18181b]/80 dark:text-[#f4f4f5]"
                                >
                                    <option value="" disabled selected>Select your account role</option>
                                    <option value="patient">Patient / Individual User</option>
                                    <option value="professional">Healthcare Professional / Doctor</option>
                                    <option value="clinic">Clinic / Medical Center</option>
                                    <option value="pharmacy">Pharmacy</option>
                                </select>
                            </div>

                            <div>
                                <label for="phone" class="block text-xs font-semibold uppercase tracking-wider text-[#57534e] dark:text-[#a1a1aa]">
                                    Phone Number (Optional - for identity verification)
                                </label>
                                <input
                                    type="tel"
                                    id="phone"
                                    name="phone"
                                    placeholder="+1 (555) 000-0000"
                                    class="mt-1.5 w-full rounded-xl border border-black/[0.1] bg-white/80 px-4 py-2.5 text-sm text-[#1c1917] shadow-sm transition placeholder:text-[#a8a29e] focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-white/[0.12] dark:bg-[#18181b]/80 dark:text-[#f4f4f5] dark:placeholder:text-[#52525b]"
                                />
                            </div>

                            <div>
                                <label for="reason" class="block text-xs font-semibold uppercase tracking-wider text-[#57534e] dark:text-[#a1a1aa]">
                                    Reason for Deletion (Optional)
                                </label>
                                <textarea
                                    id="reason"
                                    name="reason"
                                    rows="3"
                                    placeholder="Help us understand how we could improve..."
                                    class="mt-1.5 w-full rounded-xl border border-black/[0.1] bg-white/80 px-4 py-2.5 text-sm text-[#1c1917] shadow-sm transition placeholder:text-[#a8a29e] focus:border-rose-500 focus:outline-none focus:ring-2 focus:ring-rose-500/20 dark:border-white/[0.12] dark:bg-[#18181b]/80 dark:text-[#f4f4f5] dark:placeholder:text-[#52525b]"
                                ></textarea>
                            </div>

                            <div class="rounded-xl border border-rose-500/20 bg-rose-500/5 p-4 dark:border-rose-500/20 dark:bg-rose-500/10">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        required
                                        class="mt-0.5 size-4 rounded border-black/20 text-rose-600 focus:ring-rose-500 dark:border-white/20 dark:bg-[#18181b]"
                                    />
                                    <span class="text-xs leading-relaxed text-[#57534e] dark:text-[#d4d4d8]">
                                        I understand that account deletion is <strong class="text-rose-600 dark:text-rose-400">irreversible</strong>. All my booking history, profiles, stored preferences, and active reservations will be permanently deleted.
                                    </span>
                                </label>
                            </div>

                            <div class="pt-2">
                                <button
                                    type="submit"
                                    id="submit-btn"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 px-6 py-3 text-sm font-semibold text-white shadow-md shadow-rose-600/20 transition-all hover:bg-rose-700 hover:shadow-rose-600/30 active:scale-[0.99]"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>Send Deletion Request</span>
                                </button>
                            </div>
                        </form>

                        {{-- Success Notification State (Hidden by default) --}}
                        <div id="success-message" class="hidden mt-6 rounded-2xl border border-emerald-500/30 bg-emerald-500/10 p-6 text-center">
                            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-600 dark:text-emerald-400">
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <h3 class="mt-3 font-bold text-[#1c1917] dark:text-[#f4f4f5]">Request Submitted Successfully</h3>
                            <p class="mt-1.5 text-xs text-[#57534e] dark:text-[#a1a1aa]">
                                We have sent a confirmation email with identity verification instructions. Once confirmed, your account and associated data will be completely deleted within 30 days.
                            </p>
                        </div>
                    </div>

                    {{-- How to Delete Directly in App --}}
                    <div class="glass-card rounded-3xl border border-black/[0.06] p-6 shadow-sm sm:p-8 dark:border-white/[0.08]">
                        <div class="flex items-center gap-3">
                            <span class="flex size-8 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                                <svg class="size-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <h3 class="text-lg font-bold text-[#1c1917] dark:text-[#f4f4f5]">
                                Delete Immediately Inside the Rendee App
                            </h3>
                        </div>
                        <p class="mt-2 text-xs leading-relaxed text-[#78716c] dark:text-[#a1a1aa]">
                            If you still have the mobile application installed and are logged in, you can delete your account instantly without waiting for manual processing:
                        </p>

                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-xl border border-black/[0.05] bg-white/60 p-3.5 text-center dark:border-white/[0.06] dark:bg-[#18181b]/60">
                                <span class="inline-flex size-6 items-center justify-center rounded-full bg-black/5 text-xs font-bold dark:bg-white/10">1</span>
                                <p class="mt-1.5 text-xs font-medium text-[#1c1917] dark:text-[#f4f4f5]">Open Profile</p>
                                <p class="mt-0.5 text-[11px] text-[#78716c] dark:text-[#a1a1aa]">Go to your Profile tab</p>
                            </div>
                            <div class="rounded-xl border border-black/[0.05] bg-white/60 p-3.5 text-center dark:border-white/[0.06] dark:bg-[#18181b]/60">
                                <span class="inline-flex size-6 items-center justify-center rounded-full bg-black/5 text-xs font-bold dark:bg-white/10">2</span>
                                <p class="mt-1.5 text-xs font-medium text-[#1c1917] dark:text-[#f4f4f5]">Security Settings</p>
                                <p class="mt-0.5 text-[11px] text-[#78716c] dark:text-[#a1a1aa]">Tap 'Account & Privacy'</p>
                            </div>
                            <div class="rounded-xl border border-black/[0.05] bg-white/60 p-3.5 text-center dark:border-white/[0.06] dark:bg-[#18181b]/60">
                                <span class="inline-flex size-6 items-center justify-center rounded-full bg-rose-500/10 text-xs font-bold text-rose-600 dark:bg-rose-500/20 dark:text-rose-400">3</span>
                                <p class="mt-1.5 text-xs font-medium text-[#1c1917] dark:text-[#f4f4f5]">Delete Account</p>
                                <p class="mt-0.5 text-[11px] text-[#78716c] dark:text-[#a1a1aa]">Confirm account removal</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column / Info & Consequences --}}
                <div class="space-y-6 lg:col-span-5">
                    {{-- What gets deleted --}}
                    <div class="glass-panel space-y-4 rounded-3xl border border-black/[0.08] p-6 shadow-sm dark:border-white/[0.08]">
                        <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-[#78716c] dark:text-[#a1a1aa]">
                            <svg class="size-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>What Data Gets Deleted?</span>
                        </h3>

                        <ul class="space-y-3 text-xs leading-relaxed text-[#57534e] dark:text-[#d4d4d8]">
                            <li class="flex items-start gap-2.5">
                                <span class="mt-0.5 text-rose-500">✕</span>
                                <span><strong>Personal Profile:</strong> Full name, email, phone number, credentials, profile pictures, and addresses.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="mt-0.5 text-rose-500">✕</span>
                                <span><strong>Scheduling History:</strong> Appointment logs, consultation notes, proposals, and partner reviews.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="mt-0.5 text-rose-500">✕</span>
                                <span><strong>Partner Credentials:</strong> Service catalogs, medical agendas, operating hours, and specialty listings.</span>
                            </li>
                            <li class="flex items-start gap-2.5">
                                <span class="mt-0.5 text-rose-500">✕</span>
                                <span><strong>Device Tokens:</strong> Push notification endpoints, active session tokens, and location preferences.</span>
                            </li>
                        </ul>
                    </div>

                    {{-- Data Retention & Legal Requirements --}}
                    <div class="glass-card space-y-3 rounded-3xl border border-black/[0.06] p-6 shadow-sm dark:border-white/[0.08]">
                        <h4 class="text-sm font-bold text-[#1c1917] dark:text-[#f4f4f5]">
                            Data Retention & Exceptions
                        </h4>
                        <p class="text-xs leading-relaxed text-[#78716c] dark:text-[#a1a1aa]">
                            Certain non-identifiable financial transaction receipts or records mandated by healthcare compliance regulations and tax authorities may be retained in encrypted archive storage for the legally required period.
                        </p>
                    </div>

                    {{-- Contact Direct Card --}}
                    <div class="rounded-3xl border border-black/[0.08] bg-gradient-to-br from-black/[0.02] to-transparent p-6 dark:border-white/[0.08] dark:from-white/[0.03]">
                        <h4 class="text-sm font-bold text-[#1c1917] dark:text-[#f4f4f5]">
                            Need Direct Assistance?
                        </h4>
                        <p class="mt-1 text-xs text-[#78716c] dark:text-[#a1a1aa]">
                            You can also email our Data Protection Officer directly with your registered email:
                        </p>
                        <a href="mailto:privacy@rendee.app?subject=Account%20Deletion%20Request" class="mt-3 inline-flex items-center gap-2 text-xs font-semibold text-emerald-600 hover:underline dark:text-emerald-400">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>privacy@rendee.app</span>
                        </a>
                    </div>
                </div>
            </div>
        </main>

        {{-- Footer --}}
        <footer class="relative z-10 border-t border-black/[0.06] bg-[#FAFAF9]/80 py-10 text-xs text-[#78716c] backdrop-blur-md dark:border-white/[0.08] dark:bg-[#09090b]/80 dark:text-[#a1a1aa]">
            <div class="mx-auto flex max-w-5xl flex-col items-center justify-between gap-6 px-6 sm:flex-row">
                <div class="flex items-center gap-3">
                    <span class="inline-flex size-6 items-center justify-center rounded-lg bg-gradient-to-tr from-emerald-600 to-teal-500 text-[10px] font-bold text-white shadow-sm">
                        R
                    </span>
                    <span>&copy; {{ date('Y') }} Rendee Inc. All rights reserved.</span>
                </div>
                <div class="flex items-center gap-6 font-medium">
                    <a href="{{ route('privacy') }}" class="transition hover:text-black dark:hover:text-white">Privacy Policy</a>
                    <a href="{{ route('home') }}" class="transition hover:text-black dark:hover:text-white">Home</a>
                    <a href="mailto:support@rendee.app" class="transition hover:text-black dark:hover:text-white">Support</a>
                </div>
            </div>
        </footer>

        <script>
            function handleFormSubmit() {
                const email = document.getElementById('email').value;
                const role = document.getElementById('account-type').value;
                const reason = document.getElementById('reason').value;

                // Send email fallback or handle submit
                const subject = encodeURIComponent('Account Deletion Request - ' + email);
                const body = encodeURIComponent(
                    `Account Deletion Request:\n\nEmail: ${email}\nRole: ${role}\nReason: ${reason}\n\nI confirm that I want to delete my Rendee account and all associated personal data.`
                );

                // Show confirmation message
                document.getElementById('deletion-request-form').classList.add('hidden');
                document.getElementById('success-message').classList.remove('hidden');

                // Trigger mailto client as reliable direct fallback
                window.location.href = `mailto:privacy@rendee.app?subject=${subject}&body=${body}`;
            }
        </script>
    </body>
</html>
