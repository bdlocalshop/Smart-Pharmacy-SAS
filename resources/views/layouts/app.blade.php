<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Smart Pharmacy - Cloud Licensing & Offline POS Architecture' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS (CDN for instant standalone rendering) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans flex flex-col min-h-screen">
    {{-- Header / Navigation --}}
    <header x-data="{ mobileMenuOpen: false }" class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                {{-- Brand Logo --}}
                <div class="flex items-center gap-3">
                    <a href="{{ url('/') }}" class="flex items-center gap-2.5 group">
                        <img src="{{ asset('images/logo.png') }}" alt="Smart Pharmacy Logo" class="h-10 w-auto object-contain max-h-10 group-hover:scale-105 transition-transform" />
                        <span class="text-xl font-bold tracking-tight text-slate-900 flex items-center gap-1.5">
                            Smart <span class="text-brand-600">Pharmacy</span>
                        </span>
                    </a>
                </div>

                {{-- Desktop Navigation Links --}}
                <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
                    <a href="{{ url('/') }}" class="hover:text-brand-600 transition">Home</a>
                    <a href="{{ url('/#products') }}" class="hover:text-brand-600 transition">Products & Workstations</a>
                    <a href="{{ url('/#services') }}" class="hover:text-brand-600 transition">Cloud Services</a>
                    <a href="{{ route('pharmacy.directory') }}" class="hover:text-brand-600 transition flex items-center gap-1">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                        Active Pharmacies
                    </a>
                    <a href="{{ route('pharmacy.status') }}" class="hover:text-brand-600 transition flex items-center gap-1">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                        Track Status
                    </a>
                </nav>

                {{-- CTA Buttons --}}
                <div class="hidden md:flex items-center gap-3">
                    <a href="{{ route('pharmacy.register') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-medium text-sm shadow-sm transition shadow-brand-600/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" /></svg>
                        Register Pharmacy
                    </a>
                    <a href="{{ url('/admin') }}" class="inline-flex items-center gap-1 px-3 py-2 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-100 font-medium text-xs transition">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        Admin Portal
                    </a>
                </div>

                {{-- Mobile Hamburger --}}
                <div class="flex items-center md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" type="button" class="p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Menu Dropdown --}}
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-b border-slate-200 bg-white px-4 pt-2 pb-4 space-y-2">
            <a href="{{ url('/') }}" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-slate-100">Home</a>
            <a href="{{ url('/#products') }}" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-slate-100">Products & Workstations</a>
            <a href="{{ url('/#services') }}" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-slate-100">Cloud Services</a>
            <a href="{{ route('pharmacy.directory') }}" class="block px-3 py-2 rounded-md text-base font-medium text-emerald-700 hover:bg-emerald-50">Active Subscriber Pharmacies</a>
            <a href="{{ route('pharmacy.status') }}" class="block px-3 py-2 rounded-md text-base font-medium text-slate-700 hover:bg-slate-100">Track Registration Status</a>
            <div class="pt-2 border-t border-slate-100 flex flex-col gap-2">
                <a href="{{ route('pharmacy.register') }}" class="w-full text-center py-2.5 rounded-lg bg-brand-600 text-white font-medium text-sm">Register Pharmacy</a>
                <a href="{{ url('/admin') }}" class="w-full text-center py-2 rounded-lg border border-slate-200 text-slate-700 text-xs font-medium">Admin Portal</a>
            </div>
        </div>
    </header>

    {{-- Main Page Content --}}
    <main class="flex-grow">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-slate-900 text-slate-400 border-t border-slate-800 text-sm mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="space-y-4">
                    <div class="flex items-center gap-2.5">
                        <img src="{{ asset('images/logo.png') }}" alt="Smart Pharmacy Logo" class="h-8 w-auto object-contain max-h-8" />
                        <span class="text-lg font-bold text-white">Smart Pharmacy</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Offline-first retail & clinical pharmacy suite with single-device cryptographic hardware binding, automated cloud recovery, and zero-downtime reliability.
                    </p>
                </div>
                <div>
                    <h4 class="text-white text-xs font-semibold uppercase tracking-wider mb-3">Platform Solutions</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ url('/#products') }}" class="hover:text-white transition">Desktop POS Workstation</a></li>
                        <li><a href="{{ url('/#products') }}" class="hover:text-white transition">Mobile Dispense Assistant</a></li>
                        <li><a href="{{ url('/#services') }}" class="hover:text-white transition">Cloud Nightly Snapshots</a></li>
                        <li><a href="{{ url('/#services') }}" class="hover:text-white transition">Drug Interaction Feed</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white text-xs font-semibold uppercase tracking-wider mb-3">Pharmacies</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="{{ route('pharmacy.directory') }}" class="hover:text-white transition font-medium text-emerald-400">Active Subscriber Network</a></li>
                        <li><a href="{{ route('pharmacy.register') }}" class="hover:text-white transition">Self-Registration Portal</a></li>
                        <li><a href="{{ route('pharmacy.status') }}" class="hover:text-white transition">Track Verification Status</a></li>
                        <li><a href="{{ url('/admin/api-docs') }}" class="hover:text-white transition">HMAC Developer API Docs</a></li>
                        <li><a href="{{ url('/admin') }}" class="hover:text-white transition">Filament Admin Console</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white text-xs font-semibold uppercase tracking-wider mb-3">Security & Compliance</h4>
                    <p class="text-xs text-slate-400 leading-relaxed mb-3">
                        HMAC-SHA256 authenticated, replay attack protected (±300s window), and private storage encrypted backups.
                    </p>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-slate-800 text-[11px] font-mono text-emerald-400 border border-slate-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        1-Device = 1-License Active
                    </span>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-slate-800 text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p>&copy; {{ date('Y') }} Smart Pharmacy Engine. All rights reserved.</p>
                <p class="flex items-center gap-4">
                    <span>Engine v1.2</span>
                    <span>Laravel 11.x</span>
                    <span>Filament v3</span>
                </p>
            </div>
        </div>
    </footer>
</body>
</html>
