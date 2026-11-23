<x-app-layout>
    <x-slot:title>{{ __('Check Registration Status - Smart Pharmacy') }}</x-slot:title>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Flash Notification Upon Registration --}}
            @if (session('registration_success'))
                <div class="mb-8 p-6 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 shadow-sm" x-data="{ copied: false }">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        </div>
                        <div class="flex-grow">
                            <h3 class="text-base font-bold text-emerald-900">{{ __('Application Submitted Successfully!') }}</h3>
                            <p class="text-xs text-emerald-700 mt-1 leading-relaxed">
                                {{ __('Welcome') }}, <strong class="font-semibold">{{ session('pharmacy_name') }}</strong>! {{ __('Your application is in the queue for compliance verification. Please bookmark this page or record your Reference ID below:') }}
                            </p>

                            <div class="mt-3 flex items-center gap-2">
                                <span class="px-3 py-1.5 rounded-lg bg-white border border-emerald-300 font-mono text-xs font-bold text-emerald-800 select-all" id="ref-id">
                                    {{ session('registered_ref') }}
                                </span>
                                <button 
                                    type="button" 
                                    @click="navigator.clipboard.writeText('{{ session('registered_ref') }}'); copied = true; setTimeout(() => copied = false, 2500)"
                                    class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition"
                                >
                                    <span x-show="!copied">{{ __('Copy Reference ID') }}</span>
                                    <span x-show="copied" x-cloak>{{ __('Copied!') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Status Lookup Form Card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8 mb-8">
                <div class="text-center max-w-md mx-auto mb-6">
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ __('Track Application Status') }}</h2>
                    <p class="text-xs text-slate-500 mt-1">{{ __('Enter your Application Reference UUID, Trade License Number, or registered Owner Email address.') }}</p>
                </div>

                <form action="{{ route('pharmacy.status') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-grow">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        </div>
                        <input 
                            type="text" 
                            name="query" 
                            value="{{ $query }}" 
                            placeholder="e.g. 9bf3c8e4-... or TL-2026-88491-DHK or email"
                            required 
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 shadow-sm"
                        />
                    </div>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-slate-900 hover:bg-brand-600 text-white font-semibold text-sm transition">
                        {{ __('Lookup Status') }}
                    </button>
                </form>
            </div>

            {{-- Application Result Details Card --}}
            @if ($pharmacy)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    {{-- Status Header Banner --}}
                    <div class="p-6 sm:p-8 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <span class="text-[11px] font-mono text-slate-400 uppercase tracking-wider block mb-1">
                                {{ __('Application Ref:') }} {{ $pharmacy->id }}
                            </span>
                            <h3 class="text-2xl font-bold text-slate-900">{{ $pharmacy->pharmacy_name }}</h3>
                            <span class="text-xs text-slate-500">{{ __('Trade License:') }} <strong class="font-mono">{{ $pharmacy->trade_license_number }}</strong></span>
                        </div>

                        <div>
                            @php
                                $status = $pharmacy->verification_status;
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider
                                @if($status->value === 'approved') bg-emerald-100 text-emerald-800 border border-emerald-300
                                @elseif($status->value === 'pending') bg-amber-100 text-amber-800 border border-amber-300
                                @elseif($status->value === 'suspended') bg-slate-200 text-slate-800 border border-slate-300
                                @elseif($status->value === 'rejected') bg-red-100 text-red-800 border border-red-300
                                @endif
                            ">
                                <span class="w-2 h-2 rounded-full 
                                    @if($status->value === 'approved') bg-emerald-500
                                    @elseif($status->value === 'pending') bg-amber-500 animate-ping
                                    @elseif($status->value === 'suspended') bg-slate-500
                                    @elseif($status->value === 'rejected') bg-red-500
                                    @endif
                                "></span>
                                {{ $status->label() }}
                            </span>
                        </div>
                    </div>

                    {{-- Visual Progress Tracker --}}
                    <div class="p-6 sm:p-8 border-b border-slate-200 bg-white">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-6">{{ __('Verification Lifecycle') }}</h4>
                        <div class="grid grid-cols-3 gap-2 text-center text-xs">
                            {{-- Step 1 --}}
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold mb-2 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                </div>
                                <span class="font-bold text-slate-800">{{ __('Submitted') }}</span>
                                <span class="text-[10px] text-slate-400">{{ $pharmacy->created_at->format('M d, Y') }}</span>
                            </div>

                            {{-- Step 2 --}}
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full 
                                    @if($pharmacy->isApproved()) bg-emerald-500 text-white
                                    @elseif($status->value === 'pending') bg-amber-500 text-white animate-pulse
                                    @else bg-slate-200 text-slate-500
                                    @endif 
                                    flex items-center justify-center font-bold mb-2 shadow-sm">
                                    @if($pharmacy->isApproved())
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    @else
                                        2
                                    @endif
                                </div>
                                <span class="font-bold text-slate-800">{{ __('Compliance Review') }}</span>
                                <span class="text-[10px] text-slate-400">
                                    @if($pharmacy->isApproved()) {{ __('Verified') }}
                                    @elseif($status->value === 'pending') {{ __('In Progress') }}
                                    @elseif($status->value === 'rejected') {{ __('Denied') }}
                                    @else {{ __('Suspended') }}
                                    @endif
                                </span>
                            </div>

                            {{-- Step 3 --}}
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full 
                                    @if($pharmacy->activeLicense) bg-emerald-500 text-white
                                    @else bg-slate-200 text-slate-500
                                    @endif 
                                    flex items-center justify-center font-bold mb-2 shadow-sm">
                                    @if($pharmacy->activeLicense)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    @else
                                        3
                                    @endif
                                </div>
                                <span class="font-bold text-slate-800">{{ __('Hardware Locked') }}</span>
                                <span class="text-[10px] text-slate-400">
                                    @if($pharmacy->activeLicense?->bound_device_uuid) {{ __('Active Machine') }}
                                    @elseif($pharmacy->isApproved()) {{ __('Ready to Bind') }}
                                    @else {{ __('Waiting Approval') }}
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Next Step Guidance & Credentials Card --}}
                    <div class="p-6 sm:p-8 space-y-4">
                        @if($status->value === 'approved')
                            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs leading-relaxed">
                                <strong class="font-bold block text-sm mb-1">{{ __('🎉 Your pharmacy has been approved!') }}</strong>
                                {{ __('Download the Smart Pharmacy Desktop or Mobile Client, enter your License Key, and launch your offline workstation. The first device to connect will be permanently bound to your account.') }}
                            </div>

                            @if($pharmacy->activeLicense)
                                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2 text-xs">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">{{ __('License Key:') }}</span>
                                        <span class="font-mono font-bold text-slate-900 select-all">{{ $pharmacy->activeLicense->license_key }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">{{ __('Session Days Remaining:') }}</span>
                                        <span class="font-bold text-emerald-600">{{ $pharmacy->activeLicense->days_remaining }} {{ __('days') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-500">{{ __('Bound Device Lock:') }}</span>
                                        <span class="font-mono text-slate-700">
                                            {{ $pharmacy->activeLicense->bound_device_uuid ? $pharmacy->activeLicense->bound_device_uuid : __('Unbound (Ready for first device activation)') }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        @elseif($status->value === 'pending')
                            <div class="p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs leading-relaxed">
                                <strong class="font-bold block text-sm mb-1">{{ __('⏳ Application Pending Review') }}</strong>
                                {{ __('Our compliance team verifies submitted trade licenses within 24 hours. Once approved, your initial 30-day session license will be generated automatically.') }}
                            </div>
                        @elseif($status->value === 'suspended')
                            <div class="p-4 rounded-xl bg-slate-100 border border-slate-200 text-slate-800 text-xs leading-relaxed">
                                <strong class="font-bold block text-sm mb-1">{{ __('⛔ License Suspended') }}</strong>
                                {{ __('This pharmacy license is currently frozen. Please contact administrative support to reactivate your workstations.') }}
                            </div>
                        @elseif($status->value === 'rejected')
                            <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-900 text-xs leading-relaxed">
                                <strong class="font-bold block text-sm mb-1">{{ __('❌ Application Rejected') }}</strong>
                                {{ __('Your application was not approved during compliance review. Please check your submitted trade license or contact support.') }}
                            </div>
                        @endif

                        {{-- Metadata grid --}}
                        <div class="pt-4 border-t border-slate-100 grid grid-cols-2 gap-4 text-xs text-slate-600">
                            <div>
                                <span class="text-slate-400 block">{{ __('Owner Legal Name') }}</span>
                                <span class="font-semibold text-slate-800">{{ $pharmacy->owner_name }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">{{ __('Owner Contact Email') }}</span>
                                <span class="font-semibold text-slate-800">{{ $pharmacy->owner_email ?? __('Not provided') }}</span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-slate-400 block">{{ __('Storefront Physical Coordinates') }}</span>
                                <a href="{{ $pharmacy->map_location_url ?: 'https://www.google.com/maps/search/?api=1&query=' . urlencode($pharmacy->pharmacy_name . ' ' . $pharmacy->pharmacy_address) }}" target="_blank" class="text-brand-600 hover:underline flex items-center gap-1 mt-0.5">
                                    {{ $pharmacy->pharmacy_address }}
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif(!empty($query))
                {{-- Not Found Alert --}}
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8 text-center">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <h3 class="text-base font-bold text-slate-800">{{ __('No Pharmacy Application Found') }}</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">{{ __('We couldn\'t find an application matching') }} "{{ $query }}". {{ __('Please verify your UUID or Trade License number.') }}</p>
                    <a href="{{ route('pharmacy.register') }}" class="mt-4 inline-flex items-center gap-1 px-4 py-2 rounded-lg bg-brand-600 text-white text-xs font-semibold">
                        {{ __('Submit New Registration') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
