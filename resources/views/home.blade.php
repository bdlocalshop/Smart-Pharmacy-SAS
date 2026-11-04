<x-app-layout>
    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-gradient-to-b from-slate-900 via-slate-900 to-slate-800 text-white pt-20 pb-28">
        {{-- Decorative ambient background --}}
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#10b981_1px,transparent_1px)] [background-size:16px_16px]"></div>
        <div class="absolute -top-40 right-0 w-96 h-96 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 left-0 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-800/80 border border-slate-700 text-xs font-medium text-emerald-400 mb-6 shadow-inner">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Smart Pharmacy v1.2 Enterprise Engine Active
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight sm:leading-tight">
                    Offline-First POS & Centralized <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-500 to-emerald-300">Cloud Licensing</span>
                </h1>
                <p class="mt-6 text-base sm:text-lg text-slate-300 leading-relaxed font-normal">
                    Empowering clinical and retail pharmacies with resilient desktop workstations that never drop a sale during internet outages, coupled with strict 1-device cryptographic licensing and automated cloud disaster recovery snapshots.
                </p>

                <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('pharmacy.register') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-semibold text-sm shadow-lg shadow-brand-500/25 transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Register Your Pharmacy
                    </a>
                    <a href="#catalog" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 font-semibold text-sm transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
                        Explore Products & Services
                    </a>
                </div>

                {{-- Pillar Badges --}}
                <div class="mt-12 pt-8 border-t border-slate-800/80 grid grid-cols-2 md:grid-cols-4 gap-4 text-left">
                    <div class="p-3 bg-slate-800/40 rounded-lg border border-slate-700/50">
                        <div class="text-emerald-400 font-bold text-lg">100% Offline</div>
                        <div class="text-xs text-slate-400">Zero-downtime local SQLite engine</div>
                    </div>
                    <div class="p-3 bg-slate-800/40 rounded-lg border border-slate-700/50">
                        <div class="text-emerald-400 font-bold text-lg">1-Device Lock</div>
                        <div class="text-xs text-slate-400">Hardware UUID strict binding</div>
                    </div>
                    <div class="p-3 bg-slate-800/40 rounded-lg border border-slate-700/50">
                        <div class="text-emerald-400 font-bold text-lg">HMAC SHA-256</div>
                        <div class="text-xs text-slate-400">Replay-protected API security</div>
                    </div>
                    <div class="p-3 bg-slate-800/40 rounded-lg border border-slate-700/50">
                        <div class="text-emerald-400 font-bold text-lg">Cloud Backups</div>
                        <div class="text-xs text-slate-400">Private encrypted snapshot vault</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Interactive Alpine.js Catalog Section --}}
    <section id="catalog" class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
        x-data="{
            activeFilter: 'all',
            searchQuery: '',
            selectedItem: null,
            modalOpen: false,
            items: {{ Js::from($items) }},
            get filteredItems() {
                return this.items.filter(item => {
                    const matchesCategory = (this.activeFilter === 'all') || (item.item_type === this.activeFilter);
                    const q = this.searchQuery.toLowerCase().trim();
                    const matchesSearch = !q || 
                        item.title.toLowerCase().includes(q) || 
                        (item.short_description && item.short_description.toLowerCase().includes(q));
                    return matchesCategory && matchesSearch;
                });
            },
            openModal(item) {
                this.selectedItem = item;
                this.modalOpen = true;
                document.body.style.overflow = 'hidden';
            },
            closeModal() {
                this.modalOpen = false;
                document.body.style.overflow = 'auto';
            }
        }"
    >
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-brand-600 block mb-1">Storefront Showcase</span>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Products & Digital Pharmacy Services</h2>
                <p class="text-sm text-slate-500 mt-1">Browse verified workstation editions, mobile assistants, and cloud infrastructure addons.</p>
            </div>

            {{-- Live Search Input --}}
            <div class="w-full md:w-72 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                </div>
                <input 
                    type="text" 
                    x-model="searchQuery" 
                    placeholder="Search catalog items..." 
                    class="w-full pl-9 pr-4 py-2 rounded-lg border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 shadow-sm"
                />
            </div>
        </div>

        {{-- Filter Buttons Tabs --}}
        <div class="flex items-center gap-2 mb-8 overflow-x-auto pb-2 border-b border-slate-200">
            <button 
                type="button" 
                @click="activeFilter = 'all'" 
                :class="activeFilter === 'all' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" 
                class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap"
            >
                All Solutions ({{ $items->count() }})
            </button>
            <button 
                id="products"
                type="button" 
                @click="activeFilter = 'product'" 
                :class="activeFilter === 'product' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" 
                class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap flex items-center gap-1.5"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                Software & Hardware Products ({{ $productsCount }})
            </button>
            <button 
                id="services"
                type="button" 
                @click="activeFilter = 'service'" 
                :class="activeFilter === 'service' ? 'bg-brand-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" 
                class="px-4 py-2 rounded-lg text-xs font-semibold transition whitespace-nowrap flex items-center gap-1.5"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z" /></svg>
                Cloud Infrastructure Services ({{ $servicesCount }})
            </button>
        </div>

        {{-- Product Cards Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="item in filteredItems" :key="item.id">
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-md hover:border-slate-300 transition flex flex-col group">
                    {{-- Card Cover / Header --}}
                    <div class="h-44 bg-gradient-to-br from-slate-100 to-slate-200 relative overflow-hidden flex items-center justify-center">
                        <div class="w-16 h-16 rounded-2xl bg-white shadow-sm border border-slate-200 flex items-center justify-center text-brand-600 group-hover:scale-110 transition-transform">
                            <template x-if="item.item_type === 'product'">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            </template>
                            <template x-if="item.item_type === 'service'">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z" /></svg>
                            </template>
                        </div>

                        {{-- Type Badge --}}
                        <div class="absolute top-3 left-3">
                            <span 
                                :class="item.item_type === 'product' ? 'bg-sky-50 text-sky-700 border-sky-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold border uppercase tracking-wide"
                                x-text="item.item_type === 'product' ? 'Product' : 'Cloud Service'"
                            ></span>
                        </div>

                        {{-- Price Tag --}}
                        <div class="absolute top-3 right-3 bg-white/95 backdrop-blur px-2.5 py-1 rounded-lg border border-slate-200 shadow-sm text-xs font-bold text-slate-800">
                            <template x-if="item.price">
                                <span>$<span x-text="parseFloat(item.price).toFixed(2)"></span></span>
                            </template>
                            <template x-if="!item.price">
                                <span class="text-slate-500 font-medium">Custom Quote</span>
                            </template>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-6 flex-grow flex flex-col justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 group-hover:text-brand-600 transition" x-text="item.title"></h3>
                            <p class="text-xs text-slate-500 mt-2 leading-relaxed line-clamp-3" x-text="item.short_description || 'Reliable component designed for seamless Smart Pharmacy client integration.'"></p>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-[11px] font-mono text-slate-400" x-text="'SKU: ' + item.slug.substring(0, 14)"></span>
                            <button 
                                type="button" 
                                @click="openModal(item)"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-brand-600 text-white text-xs font-semibold transition"
                            >
                                Details & Specs
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Empty Search Fallback --}}
        <div x-show="filteredItems.length === 0" x-cloak class="text-center py-12 bg-white rounded-2xl border border-dashed border-slate-300 p-8">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <h4 class="text-base font-semibold text-slate-800">No matching solutions found</h4>
            <p class="text-xs text-slate-500 mt-1">Try adjusting your keyword or reset the category filter.</p>
            <button type="button" @click="activeFilter = 'all'; searchQuery = ''" class="mt-4 px-4 py-2 rounded-lg bg-brand-600 text-white text-xs font-semibold">Reset Filter</button>
        </div>

        {{-- Product Detail Modal (Alpine.js driven) --}}
        <div 
            x-show="modalOpen" 
            x-cloak 
            class="fixed inset-0 z-50 overflow-y-auto" 
            aria-labelledby="modal-title" 
            role="dialog" 
            aria-modal="true"
        >
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                {{-- Backdrop --}}
                <div 
                    x-show="modalOpen" 
                    x-transition:enter="ease-out duration-300" 
                    x-transition:enter-start="opacity-0" 
                    x-transition:enter-end="opacity-100" 
                    x-transition:leave="ease-in duration-200" 
                    x-transition:leave-start="opacity-100" 
                    x-transition:leave-end="opacity-0" 
                    @click="closeModal()" 
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
                ></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                {{-- Modal Card --}}
                <div 
                    x-show="modalOpen" 
                    x-transition:enter="ease-out duration-300" 
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                    x-transition:leave="ease-in duration-200" 
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                    class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate-200"
                >
                    <template x-if="selectedItem">
                        <div>
                            {{-- Modal Header --}}
                            <div class="p-6 bg-slate-50 border-b border-slate-200 flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl bg-white border border-slate-200 flex items-center justify-center text-brand-600 shadow-sm">
                                        <template x-if="selectedItem.item_type === 'product'">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                        </template>
                                        <template x-if="selectedItem.item_type === 'service'">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 00-9.78 2.096A4.001 4.001 0 003 15z" /></svg>
                                        </template>
                                    </div>
                                    <div>
                                        <span 
                                            :class="selectedItem.item_type === 'product' ? 'text-sky-700 bg-sky-50' : 'text-emerald-700 bg-emerald-50'"
                                            class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider mb-1"
                                            x-text="selectedItem.item_type === 'product' ? 'Workstation Product' : 'Cloud Service Addon'"
                                        ></span>
                                        <h3 class="text-xl font-bold text-slate-900" x-text="selectedItem.title"></h3>
                                    </div>
                                </div>
                                <button type="button" @click="closeModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-200/50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            {{-- Modal Body --}}
                            <div class="p-6 space-y-4 text-sm text-slate-600">
                                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                    <span class="text-xs text-slate-400 font-mono" x-text="'Identifier Slug: ' + selectedItem.slug"></span>
                                    <div class="text-lg font-extrabold text-slate-900">
                                        <template x-if="selectedItem.price">
                                            <span>$<span x-text="parseFloat(selectedItem.price).toFixed(2)"></span> <span class="text-xs font-normal text-slate-400">USD</span></span>
                                        </template>
                                        <template x-if="!selectedItem.price">
                                            <span class="text-sm font-semibold text-brand-600">Enterprise Quote</span>
                                        </template>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Overview</h4>
                                    <p class="text-slate-700 leading-relaxed font-normal" x-text="selectedItem.short_description"></p>
                                </div>

                                <template x-if="selectedItem.full_description">
                                    <div class="pt-3 border-t border-slate-100">
                                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Technical Specifications & Architecture</h4>
                                        <div class="text-xs text-slate-600 leading-relaxed prose prose-sm max-w-none" x-html="selectedItem.full_description"></div>
                                    </div>
                                </template>
                            </div>

                            {{-- Modal Footer --}}
                            <div class="p-4 bg-slate-50 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                                <span class="text-[11px] text-slate-400">Available for verified Smart Pharmacy licensed pharmacies.</span>
                                <div class="flex items-center gap-2 w-full sm:w-auto">
                                    <button type="button" @click="closeModal()" class="w-full sm:w-auto px-4 py-2 rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-semibold">
                                        Close
                                    </button>
                                    <a href="{{ route('pharmacy.register') }}" class="w-full sm:w-auto px-4 py-2 rounded-lg bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold text-center">
                                        Register Pharmacy & Inquire
                                    </a>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </section>

    {{-- Active Subscriber Pharmacies Showcase Section --}}
    <section id="pharmacies" class="py-16 bg-slate-100/70 border-t border-slate-200"
        x-data="{
            pharmacySearch: '',
            pharmacyLocation: 'all',
            matches(name, address, phone, location) {
                const query = this.pharmacySearch.toLowerCase().trim();
                const loc = this.pharmacyLocation.toLowerCase().trim();

                const matchesSearch = !query || 
                    name.includes(query) || 
                    address.includes(query) || 
                    phone.includes(query);

                const matchesLoc = !loc || loc === 'all' || 
                    address.includes(loc) || 
                    location === loc;

                return matchesSearch && matchesLoc;
            }
        }"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-8">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-semibold mb-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Subscriber Network
                    </div>
                    <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Active Subscriber Pharmacies</h2>
                    <p class="text-sm text-slate-500 mt-1">Explore certified retail & hospital pharmacies running on Smart Pharmacy Engine.</p>
                </div>
                <a href="{{ route('pharmacy.directory') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-brand-600 hover:text-brand-700 bg-white border border-slate-200 hover:border-brand-300 px-4 py-2.5 rounded-xl shadow-sm transition">
                    <span>View Dedicated Directory</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                </a>
            </div>

            {{-- Search & Filter Controls --}}
            <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-sm mb-6">
                <div class="flex flex-col md:flex-row items-stretch md:items-center gap-3">
                    {{-- Search Input --}}
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input 
                            type="text" 
                            x-model="pharmacySearch" 
                            placeholder="Search active pharmacy by name, address, or phone..."
                            class="w-full pl-10 pr-4 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition"
                        />
                    </div>

                    {{-- Location Filter --}}
                    <div class="flex items-center gap-2">
                        <div class="relative min-w-[180px]">
                            <select 
                                x-model="pharmacyLocation" 
                                class="w-full pl-3 pr-8 py-2 rounded-xl border border-slate-200 bg-slate-50/50 text-xs sm:text-sm font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:bg-white transition appearance-none cursor-pointer"
                            >
                                <option value="all">All Locations</option>
                                @foreach($pharmacyLocations as $loc)
                                    <option value="{{ strtolower($loc) }}">{{ $loc }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                            </div>
                        </div>

                        <template x-if="pharmacySearch || pharmacyLocation !== 'all'">
                            <button 
                                type="button" 
                                @click="pharmacySearch = ''; pharmacyLocation = 'all'" 
                                class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-100"
                            >
                                Reset
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Location Pills --}}
                @if(count($pharmacyLocations) > 0)
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center gap-2 flex-wrap">
                        <span class="text-[11px] font-medium text-slate-400">Filter Location:</span>
                        <button 
                            type="button" 
                            @click="pharmacyLocation = 'all'"
                            :class="pharmacyLocation === 'all' ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-2.5 py-1 rounded-md text-[11px] font-semibold transition"
                        >
                            All ({{ count($activePharmacies) }})
                        </button>
                        @foreach($pharmacyLocations as $loc)
                            <button 
                                type="button" 
                                @click="pharmacyLocation = '{{ strtolower($loc) }}'"
                                :class="pharmacyLocation === '{{ strtolower($loc) }}' ? 'bg-brand-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                class="px-2.5 py-1 rounded-md text-[11px] font-semibold transition"
                            >
                                {{ $loc }}
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Grid of Active Pharmacies --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($activePharmacies as $pharmacy)
                    <div 
                        x-show="matches(
                            '{{ strtolower(addslashes($pharmacy->pharmacy_name)) }}',
                            '{{ strtolower(addslashes($pharmacy->pharmacy_address)) }}',
                            '{{ addslashes($pharmacy->pharmacy_phone) }}',
                            '{{ strtolower(addslashes($pharmacy->estimated_location)) }}'
                        )"
                        class="bg-white rounded-2xl border border-slate-200 hover:border-brand-500/50 hover:shadow-md transition-all flex flex-col justify-between overflow-hidden"
                    >
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-700 to-emerald-500 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900">{{ $pharmacy->pharmacy_name }}</h3>
                                        <span class="text-[10px] font-mono text-slate-400">License: {{ $pharmacy->trade_license_number }}</span>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-200/60 shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Active
                                </span>
                            </div>

                            <div class="space-y-2 pt-3 border-t border-slate-100 text-xs">
                                {{-- Address --}}
                                <div class="flex items-start gap-2 text-slate-600">
                                    <svg class="w-4 h-4 text-slate-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    <span class="leading-relaxed">{{ $pharmacy->pharmacy_address }}</span>
                                </div>

                                {{-- Phone --}}
                                <div class="flex items-center gap-2 text-slate-600">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                    <a href="tel:{{ $pharmacy->pharmacy_phone }}" class="font-semibold text-slate-700 hover:text-brand-600 transition">{{ $pharmacy->pharmacy_phone }}</a>
                                </div>
                            </div>
                        </div>

                        {{-- Card Actions --}}
                        <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2">
                            <a href="tel:{{ $pharmacy->pharmacy_phone }}" class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-200/60 font-semibold text-xs flex items-center gap-1.5 transition">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                Call
                            </a>

                            {{-- Map Link opening in new tab --}}
                            <a 
                                href="{{ $pharmacy->map_location_url }}" 
                                target="_blank" 
                                rel="noopener noreferrer" 
                                class="px-3.5 py-1.5 rounded-lg bg-brand-600 hover:bg-brand-700 text-white font-semibold text-xs flex items-center gap-1.5 transition shadow-sm shadow-brand-600/20"
                            >
                                <span>Open in Map</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            @if(count($activePharmacies) === 0)
                <div class="p-8 text-center bg-white rounded-2xl border border-dashed border-slate-300 mt-4">
                    <p class="text-sm font-semibold text-slate-700">No active subscriber pharmacies registered yet.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Platform Architecture Workflow Section --}}
    <section class="py-16 bg-white border-t border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold uppercase tracking-wider text-brand-600 block mb-1">Architecture & Onboarding</span>
                <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">How Smart Pharmacy Engine Operates</h2>
                <p class="text-sm text-slate-500 mt-2">End-to-end flow from registration to offline workstation hardware binding.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col">
                    <div class="w-10 h-10 rounded-xl bg-brand-600 text-white font-bold flex items-center justify-center mb-4">1</div>
                    <h3 class="text-base font-bold text-slate-900 mb-1">Self-Registration</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Pharmacy submits business info, trade license, owner contact, and verified physical map pin.</p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-bold flex items-center justify-center mb-4">2</div>
                    <h3 class="text-base font-bold text-slate-900 mb-1">Admin Verification</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Admin reviews documentation on Filament panel and approves with initial session days (default: 30d).</p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col">
                    <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white font-bold flex items-center justify-center mb-4">3</div>
                    <h3 class="text-base font-bold text-slate-900 mb-1">1-Device Lock</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Workstation validates via HMAC-SHA256. The physical machine UUID is bound strictly to the license.</p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col">
                    <div class="w-10 h-10 rounded-xl bg-purple-600 text-white font-bold flex items-center justify-center mb-4">4</div>
                    <h3 class="text-base font-bold text-slate-900 mb-1">Nightly Cloud Sync</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">Automated compressed SQLite snapshots push to private cloud storage with tamper validation.</p>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
