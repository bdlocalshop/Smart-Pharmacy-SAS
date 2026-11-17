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
                    Search and locate licensed pharmacies by name, address, phone, or paste a Google Maps link / coordinates to find the nearest partner branch within your chosen distance.
                </p>
            </div>
        </div>
    </div>

    {{-- Main Directory Container with Reactive Alpine.js Proximity & Text Filtering --}}
    <div 
        id="pharmacies-results-container"
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10"
        x-data="{
            allPharmacies: {{ Js::from($pharmaciesJson) }},
            searchQuery: '{{ addslashes($initialSearch) }}',
            selectedLocation: '{{ addslashes($initialLocation) }}',
            distanceKm: {{ $initialDistance ?? 0.5 }},
            locating: false,
            perPage: 10,
            currentPage: 1,

            init() {
                this.$watch('searchQuery', () => { this.currentPage = 1; });
                this.$watch('selectedLocation', () => { this.currentPage = 1; });
                this.$watch('distanceKm', () => { this.currentPage = 1; });
            },

            // Extract lat & lng from Google Maps link or raw coordinates string
            extractCoords(text) {
                if (!text || typeof text !== 'string') return null;
                const str = text.trim();
                const match = str.match(/(?:[?&](?:q|query|ll|loc)=|@)?(-?\d{1,2}(?:\.\d+)?)[,\s]+(-?\d{1,3}(?:\.\d+)?)/i);
                if (!match) return null;
                const lat = parseFloat(match[1]);
                const lng = parseFloat(match[2]);
                if (lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                    return { lat, lng };
                }
                return null;
            },

            // Calculate Haversine Great Circle distance in km
            haversineKm(lat1, lon1, lat2, lon2) {
                const R = 6371;
                const toRad = (x) => (x * Math.PI) / 180;
                const dLat = toRad(lat2 - lat1);
                const dLon = toRad(lon2 - lon1);
                const a =
                    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                    Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                    Math.sin(dLon / 2) * Math.sin(dLon / 2);
                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                return Math.round(R * c * 100) / 100;
            },

            get searchCoords() {
                return this.extractCoords(this.searchQuery);
            },

            get isProximityMode() {
                return this.searchCoords !== null;
            },

            // Computed pharmacies with calculated distances and sorting
            get computedPharmacies() {
                const coords = this.searchCoords;
                const loc = this.selectedLocation.toLowerCase().trim();
                const query = this.searchQuery.toLowerCase().trim();

                // Calculate distance for all pharmacies if coordinates are present
                let list = this.allPharmacies.map(p => {
                    let d = null;
                    if (coords && p.lat !== null && p.lng !== null) {
                        d = this.haversineKm(coords.lat, coords.lng, p.lat, p.lng);
                    }
                    return { ...p, distance: d };
                });

                // Filter by location dropdown if selected
                if (loc && loc !== 'all') {
                    list = list.filter(p => 
                        (p.address && p.address.toLowerCase().includes(loc)) ||
                        (p.location && p.location.toLowerCase() === loc)
                    );
                }

                if (coords) {
                    // Sort closest to farthest
                    list.sort((a, b) => {
                        const distA = a.distance !== null ? a.distance : 999999;
                        const distB = b.distance !== null ? b.distance : 999999;
                        return distA - distB;
                    });
                } else if (query) {
                    // Standard text search: name, address, phone, map link
                    list = list.filter(p => 
                        (p.name && p.name.toLowerCase().includes(query)) ||
                        (p.address && p.address.toLowerCase().includes(query)) ||
                        (p.phone && p.phone.toLowerCase().includes(query)) ||
                        (p.map_url && p.map_url.toLowerCase().includes(query))
                    );
                }

                return list;
            },

            // Pharmacies strictly within the selected distance range (or matching text query)
            get inRangePharmacies() {
                if (this.isProximityMode) {
                    return this.computedPharmacies.filter(p => p.distance !== null && p.distance <= this.distanceKm);
                }
                return this.computedPharmacies;
            },

            // Closest fallback pharmacies when none match within the selected range
            get closestFallbackPharmacies() {
                if (this.isProximityMode && this.inRangePharmacies.length === 0) {
                    return this.computedPharmacies.filter(p => p.distance !== null).slice(0, 3);
                }
                return [];
            },

            get hasNoRangeMatches() {
                return this.isProximityMode && this.inRangePharmacies.length === 0 && this.computedPharmacies.length > 0;
            },

            // Active list to paginate (either regular in-range or nearest fallbacks)
            get activeResultsList() {
                return this.hasNoRangeMatches ? this.closestFallbackPharmacies : this.inRangePharmacies;
            },

            get totalResults() {
                return this.activeResultsList.length;
            },

            get totalPages() {
                return Math.max(1, Math.ceil(this.totalResults / this.perPage));
            },

            get pagedPharmacies() {
                const start = (this.currentPage - 1) * this.perPage;
                return this.activeResultsList.slice(start, start + this.perPage);
            },

            setPage(p) {
                if (p >= 1 && p <= this.totalPages) {
                    this.currentPage = p;
                    this.scrollToResults();
                }
            },

            prevPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                    this.scrollToResults();
                }
            },

            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                    this.scrollToResults();
                }
            },

            scrollToResults() {
                const el = document.getElementById('pharmacies-results-container');
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            },

            get pageNumbers() {
                const total = this.totalPages;
                const current = this.currentPage;
                if (total <= 7) {
                    return Array.from({ length: total }, (_, i) => i + 1);
                }
                const pages = [];
                pages.push(1);
                if (current > 3) pages.push('...');
                const start = Math.max(2, current - 1);
                const end = Math.min(total - 1, current + 1);
                for (let i = start; i <= end; i++) {
                    pages.push(i);
                }
                if (current < total - 2) pages.push('...');
                pages.push(total);
                return pages;
            },

            // GPS auto-locate handler
            useMyLocation() {
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by your browser.');
                    return;
                }
                this.locating = true;
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        const lat = pos.coords.latitude.toFixed(4);
                        const lng = pos.coords.longitude.toFixed(4);
                        this.searchQuery = `${lat}, ${lng}`;
                        this.locating = false;
                    },
                    (err) => {
                        this.locating = false;
                        alert('Could not retrieve your location. Please check browser location permissions or paste your Google Maps link.');
                    },
                    { enableHighAccuracy: true, timeout: 8000 }
                );
            },

            resetFilters() {
                this.searchQuery = '';
                this.selectedLocation = 'all';
                this.distanceKm = 0.5;
                this.currentPage = 1;
            }
        }"
    >
        {{-- Search & Proximity Filter Card --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm mb-8 space-y-5">
            {{-- Top Row: Search Input + Location Dropdown --}}
            <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
                {{-- Search Input Field with 200ms debounce for lightning speed --}}
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        x-model.debounce.200ms="searchQuery" 
                        placeholder="Search by pharmacy name, address, phone, Google Maps link, or coordinates..."
                        class="w-full pl-10 pr-24 py-2.5 rounded-xl border border-slate-300 bg-slate-50/50 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white transition"
                    />

                    {{-- Right Actions inside Search Bar: Clear & GPS --}}
                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center gap-1">
                        <template x-if="searchQuery">
                            <button 
                                type="button" 
                                @click="searchQuery = ''" 
                                class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition"
                                title="Clear search"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </template>

                        <button 
                            type="button" 
                            @click="useMyLocation()" 
                            class="px-2.5 py-1 text-xs font-semibold rounded-lg bg-slate-100 hover:bg-brand-50 hover:text-brand-600 text-slate-700 transition flex items-center gap-1 border border-slate-200"
                            :title="locating ? 'Detecting your GPS position...' : 'Use My Current Location'"
                        >
                            <svg class="w-3.5 h-3.5 text-brand-600" :class="{ 'animate-spin': locating }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="hidden sm:inline" x-text="locating ? 'Locating...' : 'GPS'"></span>
                        </button>
                    </div>
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
                    <template x-if="searchQuery || selectedLocation !== 'all' || distanceKm !== 0.5">
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

            {{-- Dedicated Distance Selector (Horizontal Range Slider: 0.25 km to 10 km, default 0.5 km) --}}
            <div 
                class="pt-4 border-t border-slate-100 rounded-xl p-4 transition-colors duration-200"
                :class="isProximityMode ? 'bg-brand-50/50 border border-brand-200/80 shadow-xs' : 'bg-slate-50/60'"
            >
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2.5">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        <label class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                            Distance Selector (Proximity Radius)
                        </label>
                        <span 
                            class="text-xs font-extrabold font-mono px-2.5 py-0.5 rounded-md bg-white border border-slate-200 text-brand-600 shadow-2xs"
                            x-text="distanceKm < 1 ? (distanceKm * 1000).toFixed(0) + ' m (' + Number(distanceKm).toFixed(2) + ' km)' : Number(distanceKm).toFixed(2) + ' km'"
                        >
                            0.50 km
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <template x-if="isProximityMode">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Proximity Mode Active: Closest First
                            </span>
                        </template>
                        <template x-if="!isProximityMode">
                            <span class="text-[11px] text-slate-500 italic">
                                Paste a Google Maps link or lat,lng to activate live closest-distance sorting
                            </span>
                        </template>
                    </div>
                </div>

                {{-- The Horizontal Scrollbar / Range Slider (Min: 0.25 km, Max: 10 km, Default: 0.5 km) --}}
                <div class="space-y-1.5">
                    <input 
                        type="range" 
                        min="0.25" 
                        max="10" 
                        step="0.05" 
                        x-model.number="distanceKm"
                        class="w-full h-2.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-brand-600 focus:outline-none transition"
                        title="Adjust proximity distance (0.25 km to 10 km)"
                    />
                    <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono">
                        <span>0.25 km (250m)</span>
                        <div class="flex items-center gap-2 sm:gap-3 flex-wrap justify-center">
                            <button type="button" @click="distanceKm = 0.25" :class="distanceKm === 0.25 ? 'text-brand-600 font-bold underline' : 'hover:text-slate-600'">0.25 km</button>
                            <button type="button" @click="distanceKm = 0.5" :class="distanceKm === 0.5 ? 'text-brand-600 font-bold underline' : 'hover:text-slate-600'">0.5 km (Default)</button>
                            <button type="button" @click="distanceKm = 1" :class="distanceKm === 1 ? 'text-brand-600 font-bold underline' : 'hover:text-slate-600'">1 km</button>
                            <button type="button" @click="distanceKm = 2.5" :class="distanceKm === 2.5 ? 'text-brand-600 font-bold underline' : 'hover:text-slate-600'">2.5 km</button>
                            <button type="button" @click="distanceKm = 5" :class="distanceKm === 5 ? 'text-brand-600 font-bold underline' : 'hover:text-slate-600'">5 km</button>
                            <button type="button" @click="distanceKm = 10" :class="distanceKm === 10 ? 'text-brand-600 font-bold underline' : 'hover:text-slate-600'">10 km (Max)</button>
                        </div>
                        <span>10 km (Max)</span>
                    </div>
                </div>
            </div>

            {{-- Location Pill Shortcuts --}}
            @if(count($locations) > 0)
                <div class="pt-3 border-t border-slate-100 flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-medium text-slate-400 mr-1">Quick Region:</span>
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
                Active certified subscriber pharmacies: 
                <span class="font-bold text-slate-800" x-text="totalResults"></span>
                <span class="text-slate-400" x-text="'(10 per page)'"></span>
                <template x-if="isProximityMode">
                    <span class="ml-2 text-emerald-600 font-semibold">(Sorted by closest distance first)</span>
                </template>
            </div>
            <div class="flex items-center gap-1.5 text-xs text-emerald-600 font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                100% Cryptographically Verified
            </div>
        </div>

        {{-- Warning Banner: When nothing is available within the selected distance range --}}
        <template x-if="hasNoRangeMatches">
            <div class="bg-amber-50 border border-amber-200/90 rounded-2xl p-6 mb-8 text-center shadow-xs">
                <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-amber-900">
                    Nothing is available within the selected area (<span x-text="distanceKm < 1 ? (distanceKm * 1000).toFixed(0) + ' m' : Number(distanceKm).toFixed(2) + ' km'"></span>)
                </h3>
                <p class="text-xs text-amber-700 mt-1.5 max-w-lg mx-auto leading-relaxed">
                    No registered pharmacies were found within your chosen radius. The closest pharmacies to your location outside this range are shown below:
                </p>
                <div class="mt-4 flex items-center justify-center gap-2">
                    <button 
                        type="button" 
                        @click="distanceKm = Math.min(10, Math.max(0.25, Math.ceil((closestFallbackPharmacies[0]?.distance || 0.5) * 4) / 4))" 
                        class="px-4 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold shadow-xs transition"
                    >
                        Expand radius to include closest (<span x-text="Math.min(10, Math.max(0.25, Math.ceil((closestFallbackPharmacies[0]?.distance || 0.5) * 4) / 4)) + ' km'"></span>)
                    </button>
                </div>
            </div>
        </template>

        {{-- Fallback Header when showing pharmacies outside selected radius --}}
        <template x-if="hasNoRangeMatches && closestFallbackPharmacies.length > 0">
            <div class="mb-4 flex items-center justify-between">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    Closest Available Pharmacies (Outside Selected Range)
                </h4>
                <span class="text-xs text-slate-400">Nearest available branches</span>
            </div>
        </template>

        {{-- Pharmacy Cards Grid (10 items per page for instant render) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="(pharmacy, index) in pagedPharmacies" :key="pharmacy.id">
                <div class="bg-white rounded-2xl border border-slate-200/90 hover:border-brand-500/50 hover:shadow-lg transition-all duration-200 flex flex-col justify-between overflow-hidden group">
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
                                    <h3 class="text-lg font-bold text-slate-900 group-hover:text-brand-600 transition" x-text="pharmacy.name"></h3>
                                    <span class="text-[11px] font-mono text-slate-400" x-text="'Reg: ' + pharmacy.trade_license"></span>
                                </div>
                            </div>
                            
                            {{-- Badges --}}
                            <div class="flex flex-col items-end gap-1.5 shrink-0">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Active
                                </span>

                                {{-- Distance Pill (Visible in Proximity Mode) --}}
                                <template x-if="pharmacy.distance !== null">
                                    <span 
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold font-mono"
                                        :class="index === 0 && currentPage === 1 && !hasNoRangeMatches ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-brand-50 text-brand-700 border border-brand-200'"
                                    >
                                        <svg class="w-3 h-3 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                        <span x-text="pharmacy.distance < 1 ? (pharmacy.distance * 1000).toFixed(0) + ' m' : pharmacy.distance.toFixed(2) + ' km'"></span>
                                        <template x-if="index === 0 && currentPage === 1 && !hasNoRangeMatches">
                                            <span class="text-[10px] font-semibold text-emerald-700 uppercase tracking-tight ml-0.5">Closest</span>
                                        </template>
                                    </span>
                                </template>
                            </div>
                        </div>

                        {{-- Opening Hours Slots --}}
                        <template x-if="pharmacy.opening_hours_slots && pharmacy.opening_hours_slots.length > 0">
                            <div class="mt-3 pt-2.5 border-t border-slate-100">
                                <div class="flex items-center gap-1.5 text-[11px] font-semibold text-slate-500 mb-1.5">
                                    <svg class="w-3.5 h-3.5 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>Opening Hours</span>
                                </div>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <template x-for="slot in pharmacy.opening_hours_slots" :key="slot">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-mono font-medium bg-slate-50 border border-slate-200 text-slate-700 shadow-2xs group-hover:border-brand-200 group-hover:bg-brand-50/40 transition-colors">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5 shrink-0"></span>
                                            <span x-text="slot"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </template>

                        {{-- Pharmacy Details --}}
                        <div class="mt-4 space-y-3 pt-3 border-t border-slate-100 text-xs">
                            {{-- Address --}}
                            <div class="flex items-start gap-2.5 text-slate-600">
                                <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="leading-relaxed" x-text="pharmacy.address"></span>
                            </div>

                            {{-- Phone Number --}}
                            <div class="flex items-center gap-2.5 text-slate-600">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <a 
                                    :href="'tel:' + pharmacy.phone" 
                                    class="font-semibold text-slate-700 hover:text-brand-600 hover:underline transition"
                                    x-text="pharmacy.phone"
                                ></a>
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
                            :href="'tel:' + pharmacy.phone" 
                            class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-200/60 font-semibold text-xs flex items-center gap-1.5 transition"
                        >
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            Call
                        </a>

                        <a 
                            :href="pharmacy.map_url" 
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
            </template>
        </div>

        {{-- Pagination Bar (10 results per page) --}}
        <template x-if="totalResults > 0">
            <div class="mt-8 pt-5 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                {{-- Left: Results Count --}}
                <div class="text-xs text-slate-500">
                    Showing <span class="font-bold text-slate-800" x-text="totalResults === 0 ? 0 : ((currentPage - 1) * perPage + 1)"></span> to 
                    <span class="font-bold text-slate-800" x-text="Math.min(currentPage * perPage, totalResults)"></span> of 
                    <span class="font-bold text-slate-800" x-text="totalResults"></span> pharmacies
                    <span class="text-slate-400 font-mono">(Page <span x-text="currentPage"></span> of <span x-text="totalPages"></span>)</span>
                </div>

                {{-- Right: Pagination Buttons --}}
                <template x-if="totalPages > 1">
                    <nav class="flex items-center gap-1">
                        {{-- Previous Button --}}
                        <button 
                            type="button" 
                            @click="prevPage()" 
                            :disabled="currentPage === 1"
                            :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed text-slate-400 bg-slate-50' : 'text-slate-700 bg-white hover:bg-slate-100 hover:border-brand-500'"
                            class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold transition flex items-center gap-1 shadow-2xs"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                            Previous
                        </button>

                        {{-- Page Numbers --}}
                        <template x-for="p in pageNumbers" :key="p">
                            <div>
                                <template x-if="p === '...'">
                                    <span class="px-2 py-1 text-xs text-slate-400 select-none">...</span>
                                </template>
                                <template x-if="p !== '...'">
                                    <button 
                                        type="button" 
                                        @click="setPage(p)" 
                                        :class="currentPage === p ? 'bg-brand-600 text-white border-brand-600 font-bold shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border-slate-200'"
                                        class="w-8 h-8 rounded-lg border text-xs font-semibold flex items-center justify-center transition shadow-2xs"
                                        x-text="p"
                                    ></button>
                                </template>
                            </div>
                        </template>

                        {{-- Next Button --}}
                        <button 
                            type="button" 
                            @click="nextPage()" 
                            :disabled="currentPage === totalPages"
                            :class="currentPage === totalPages ? 'opacity-40 cursor-not-allowed text-slate-400 bg-slate-50' : 'text-slate-700 bg-white hover:bg-slate-100 hover:border-brand-500'"
                            class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold transition flex items-center gap-1 shadow-2xs"
                        >
                            Next
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </button>
                    </nav>
                </template>
            </div>
        </template>

        {{-- Initial Server-Side Rendered Fallback (Hidden Once Alpine Loads) --}}
        <div x-cloak class="hidden">
            @foreach($pharmacies as $pharmacy)
                <div class="ssr-pharmacy-card">
                    <h4>{{ $pharmacy->pharmacy_name }}</h4>
                    <p>{{ $pharmacy->pharmacy_address }}</p>
                    <p>{{ $pharmacy->pharmacy_phone }}</p>
                    @if($pharmacy->opening_hours)
                        <p>Opening Hours: {{ $pharmacy->opening_hours }}</p>
                    @endif
                    <a href="{{ $pharmacy->map_location_url ?: 'https://www.google.com/maps/search/?api=1&query=' . urlencode($pharmacy->pharmacy_name . ' ' . $pharmacy->pharmacy_address) }}" target="_blank">Open in Map</a>
                    <span>Active</span>
                </div>
            @endforeach
        </div>

        {{-- Empty State when no pharmacies exist in directory --}}
        <template x-if="inRangePharmacies.length === 0 && closestFallbackPharmacies.length === 0">
            <div class="p-12 text-center bg-white rounded-2xl border border-dashed border-slate-300 mt-6">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">No matching pharmacies found</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                    Try adjusting your search terms, changing the region filter, or resetting your search filters.
                </p>
                <button 
                    type="button"
                    @click="resetFilters()" 
                    class="mt-4 inline-block px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold shadow-sm transition"
                >
                    Reset All Filters
                </button>
            </div>
        </template>
    </div>
</x-app-layout>
