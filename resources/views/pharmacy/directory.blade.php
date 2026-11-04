<x-app-layout>
    {{-- Header Banner --}}
    <div class="bg-gradient-to-b from-slate-900 to-slate-800 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-xs font-semibold text-emerald-400 mb-4">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Verified Subscriber Network
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">
                    Active Subscriber Pharmacies
                </h1>
                <p class="mt-3 text-slate-300 text-sm sm:text-base leading-relaxed">
                    Browse licensed pharmacies actively operating with Smart Pharmacy offline POS workstations and secured cloud sync. Search by pharmacy name or filter by location to locate a partner branch near you.
                </p>
            </div>
        </div>
    </div>

    {{-- Main Directory Container with Alpine.js Reactive Filtering --}}
    <div 
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10"
        x-data="{
            searchQuery: '{{ addslashes($initialSearch) }}',
            selectedLocation: '{{ addslashes($initialLocation) }}',
            matches(name, address, phone, location) {
                const query = this.searchQuery.toLowerCase().trim();
                const loc = this.selectedLocation.toLowerCase().trim();

                const matchesSearch = !query || 
                    name.includes(query) || 
                    address.includes(query) || 
                    phone.includes(query);

                const matchesLocation = !loc || loc === 'all' || 
                    address.includes(loc) || 
                    location === loc;

                return matchesSearch && matchesLocation;
            },
            resetFilters() {
                this.searchQuery = '';
                this.selectedLocation = 'all';
            }
        }"
    >
        {{-- Search & Location Filter Bar --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm mb-8">
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                {{-- Search Input Field --}}
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        x-model="searchQuery" 
                        placeholder="Search by pharmacy name, address, or phone number..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 bg-slate-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white transition"
                    />
                    <template x-if="searchQuery">
                        <button 
                            type="button" 
                            @click="searchQuery = ''" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </template>
                </div>

                {{-- Location Filter Dropdown --}}
                <div class="flex items-center gap-2">
                    <div class="relative min-w-[200px]">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </div>
                        <select 
                            x-model="selectedLocation" 
                            class="w-full pl-9 pr-8 py-2.5 rounded-xl border border-slate-300 bg-slate-50/50 text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white transition appearance-none cursor-pointer"
                        >
                            <option value="all">All Locations</option>
                            @foreach($locations as $loc)
                                <option value="{{ strtolower($loc) }}">{{ $loc }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                        </div>
                    </div>

                    {{-- Reset Button --}}
                    <template x-if="searchQuery || selectedLocation !== 'all'">
                        <button 
                            type="button" 
                            @click="resetFilters()" 
                            class="px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-100 transition whitespace-nowrap"
                        >
                            Reset
                        </button>
                    </template>
                </div>
            </div>

            {{-- Location Pill Shortcuts --}}
            @if(count($locations) > 0)
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-medium text-slate-400 mr-1">Quick Filter:</span>
                    <button 
                        type="button" 
                        @click="selectedLocation = 'all'"
                        :class="selectedLocation === 'all' ? 'bg-brand-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                        class="px-3 py-1 rounded-lg text-xs font-semibold transition"
                    >
                        All Regions ({{ $totalCount }})
                    </button>
                    @foreach($locations as $loc)
                        <button 
                            type="button" 
                            @click="selectedLocation = '{{ strtolower($loc) }}'"
                            :class="selectedLocation === '{{ strtolower($loc) }}' ? 'bg-brand-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-3 py-1 rounded-lg text-xs font-semibold transition flex items-center gap-1"
                        >
                            <span>{{ $loc }}</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Live Results Header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="text-xs text-slate-500 font-medium">
                Active certified subscriber pharmacies in network: <span class="font-bold text-slate-800">{{ $totalCount }}</span>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                100% Cryptographically Verified
            </div>
        </div>

        {{-- Pharmacy Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($pharmacies as $pharmacy)
                <div 
                    x-show="matches(
                        '{{ strtolower(addslashes($pharmacy->pharmacy_name)) }}',
                        '{{ strtolower(addslashes($pharmacy->pharmacy_address)) }}',
                        '{{ addslashes($pharmacy->pharmacy_phone) }}',
                        '{{ strtolower(addslashes($pharmacy->estimated_location)) }}'
                    )"
                    class="bg-white rounded-2xl border border-slate-200/90 hover:border-brand-500/50 hover:shadow-lg transition-all duration-200 flex flex-col justify-between overflow-hidden group"
                >
                    {{-- Card Header --}}
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-brand-600 to-emerald-500 text-white flex items-center justify-center font-bold text-base shadow-sm shadow-brand-500/20 group-hover:scale-105 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-brand-600 transition">{{ $pharmacy->pharmacy_name }}</h3>
                                    <span class="text-[11px] font-mono text-slate-400">Reg: {{ $pharmacy->trade_license_number }}</span>
                                </div>
                            </div>
                            
                            {{-- Active Badge --}}
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200/60 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Active
                            </span>
                        </div>

                        {{-- Pharmacy Details --}}
                        <div class="mt-4 space-y-3 pt-3 border-t border-slate-100 text-xs">
                            {{-- Address --}}
                            <div class="flex items-start gap-2.5 text-slate-600">
                                <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="leading-relaxed">{{ $pharmacy->pharmacy_address }}</span>
                            </div>

                            {{-- Phone Number --}}
                            <div class="flex items-center gap-2.5 text-slate-600">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <a 
                                    href="tel:{{ $pharmacy->pharmacy_phone }}" 
                                    class="font-semibold text-slate-700 hover:text-brand-600 hover:underline transition"
                                >
                                    {{ $pharmacy->pharmacy_phone }}
                                </a>
                            </div>

                            {{-- Hardware / License Note --}}
                            <div class="flex items-center gap-2.5 text-slate-500 pt-1">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                                <span>1-Device Hardware Binding & Cloud Backup Active</span>
                            </div>
                        </div>
                    </div>

                    {{-- Card Footer with Map Link Opening in New Tab --}}
                    <div class="p-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between gap-3">
                        <a 
                            href="tel:{{ $pharmacy->pharmacy_phone }}" 
                            class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-200/60 font-semibold text-xs flex items-center gap-1.5 transition"
                        >
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            Call
                        </a>

                        <a 
                            href="{{ $pharmacy->map_location_url ?: 'https://www.google.com/maps/search/?api=1&query=' . urlencode($pharmacy->pharmacy_name . ' ' . $pharmacy->pharmacy_address) }}" 
                            target="_blank" 
                            rel="noopener noreferrer" 
                            class="px-3.5 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs flex items-center gap-1.5 transition shadow-sm shadow-brand-600/20"
                        >
                            <span>Open in Map</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        @if(count($pharmacies) === 0)
            <div class="p-12 text-center bg-white rounded-2xl border border-dashed border-slate-300">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">No active subscriber pharmacies registered yet</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                    Approved pharmacies with active licenses will automatically appear in this public directory.
                </p>
                <a 
                    href="{{ route('pharmacy.register') }}" 
                    class="mt-4 inline-block px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm transition"
                >
                    Register Your Pharmacy
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
