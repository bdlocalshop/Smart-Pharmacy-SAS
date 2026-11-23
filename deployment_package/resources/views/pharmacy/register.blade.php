<x-app-layout>
    <x-slot:title>{{ __('Pharmacy Self-Registration - Smart Pharmacy') }}</x-slot:title>

    <div class="py-12 bg-slate-50 min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Header Title --}}
            <div class="text-center mb-8">
                <span class="text-xs font-bold uppercase tracking-wider text-brand-600 block mb-1">{{ __('Onboarding Portal') }}</span>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">{{ __('Pharmacy Registration Application') }}</h1>
                <p class="text-sm text-slate-500 mt-2 max-w-xl mx-auto">
                    {{ __('Submit your enterprise credentials and physical store location to obtain an offline workstation license and access to the cloud backup repository.') }}
                </p>
            </div>

            {{-- Global Validation Errors Alert --}}
            @if ($errors->any())
                <div class="mb-8 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 shadow-sm flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="text-xs space-y-1">
                        <span class="font-bold text-sm block">{{ __('Please correct the following errors before submitting:') }}</span>
                        <ul class="list-disc list-inside space-y-0.5 text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{-- Registration Form Card --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <form action="{{ route('pharmacy.register.store') }}" method="POST" class="p-6 sm:p-10 space-y-8">
                    @csrf

                    {{-- Step 1: Pharmacy Business Info --}}
                    <div>
                        <div class="flex items-center gap-3 pb-3 border-b border-slate-200 mb-6">
                            <span class="w-8 h-8 rounded-lg bg-brand-50 text-brand-700 font-bold text-sm flex items-center justify-center border border-brand-200">1</span>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">{{ __('Pharmacy Business Credentials') }}</h2>
                                <p class="text-xs text-slate-500">{{ __('Government trade license & verified physical location.') }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-sm">
                            {{-- Pharmacy Name --}}
                            <div>
                                <label for="pharmacy_name" class="block font-semibold text-slate-700 mb-1">{{ __('Pharmacy Name') }} <span class="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    name="pharmacy_name" 
                                    id="pharmacy_name" 
                                    value="{{ old('pharmacy_name') }}" 
                                    required 
                                    placeholder="e.g. Apex Care Pharmacy"
                                    class="w-full px-3.5 py-2.5 rounded-lg border @error('pharmacy_name') border-red-500 focus:ring-red-500 @else border-slate-300 focus:ring-brand-500 @enderror focus:outline-none focus:ring-2 bg-white text-sm"
                                />
                                @error('pharmacy_name')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Trade License Number --}}
                            <div>
                                <label for="trade_license_number" class="block font-semibold text-slate-700 mb-1">{{ __('Trade License Number') }} <span class="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    name="trade_license_number" 
                                    id="trade_license_number" 
                                    value="{{ old('trade_license_number') }}" 
                                    required 
                                    placeholder="e.g. TL-2026-88491-DHK"
                                    class="w-full px-3.5 py-2.5 rounded-lg border @error('trade_license_number') border-red-500 focus:ring-red-500 @else border-slate-300 focus:ring-brand-500 @enderror focus:outline-none focus:ring-2 bg-white text-sm"
                                />
                                @error('trade_license_number')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Pharmacy Phone --}}
                            <div>
                                <label for="pharmacy_phone" class="block font-semibold text-slate-700 mb-1">{{ __('Pharmacy Official Phone') }} <span class="text-red-500">*</span></label>
                                <input 
                                    type="tel" 
                                    name="pharmacy_phone" 
                                    id="pharmacy_phone" 
                                    value="{{ old('pharmacy_phone') }}" 
                                    required 
                                    placeholder="+8801711002233"
                                    class="w-full px-3.5 py-2.5 rounded-lg border @error('pharmacy_phone') border-red-500 focus:ring-red-500 @else border-slate-300 focus:ring-brand-500 @enderror focus:outline-none focus:ring-2 bg-white text-sm"
                                />
                                <span class="text-[11px] text-slate-400 mt-0.5 block">{{ __('Include country code for verification SMS/call.') }}</span>
                                @error('pharmacy_phone')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Map Location URL --}}
                            <div>
                                <label for="map_location_url" class="block font-semibold text-slate-700 mb-1">{{ __('Map Pin Link (Google Maps / OSM)') }}</label>
                                <input 
                                    type="url" 
                                    name="map_location_url" 
                                    id="map_location_url" 
                                    value="{{ old('map_location_url') }}" 
                                    placeholder="https://maps.google.com/?q=23.7925,90.4078"
                                    class="w-full px-3.5 py-2.5 rounded-lg border @error('map_location_url') border-red-500 focus:ring-red-500 @else border-slate-300 focus:ring-brand-500 @enderror focus:outline-none focus:ring-2 bg-white text-sm"
                                />
                                <span class="text-[11px] text-slate-400 mt-0.5 block">{{ __('Direct link to your pharmacy physical storefront coordinates (optional).') }}</span>
                                @error('map_location_url')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Opening Hours Slots --}}
                            <div>
                                <label for="opening_hours" class="block font-semibold text-slate-700 mb-1">{{ __('Opening Hours (Slots)') }}</label>
                                <input 
                                    type="text" 
                                    name="opening_hours" 
                                    id="opening_hours" 
                                    value="{{ old('opening_hours') }}" 
                                    placeholder="8:00AM-1:00PM, 2:00PM-5:00PM, 6:00PM-11:30PM"
                                    class="w-full px-3.5 py-2.5 rounded-lg border @error('opening_hours') border-red-500 focus:ring-red-500 @else border-slate-300 focus:ring-brand-500 @enderror focus:outline-none focus:ring-2 bg-white text-sm"
                                />
                                <span class="text-[11px] text-slate-400 mt-0.5 block">{{ __('Enter time slots separated by commas (optional).') }}</span>
                                @error('opening_hours')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Pharmacy Physical Address --}}
                            <div class="md:col-span-2">
                                <label for="pharmacy_address" class="block font-semibold text-slate-700 mb-1">{{ __('Full Physical Pharmacy Address') }} <span class="text-red-500">*</span></label>
                                <textarea 
                                    name="pharmacy_address" 
                                    id="pharmacy_address" 
                                    rows="2" 
                                    required 
                                    placeholder="Shop 4, Building B, Main Road, City, Postal Code"
                                    class="w-full px-3.5 py-2.5 rounded-lg border @error('pharmacy_address') border-red-500 focus:ring-red-500 @else border-slate-300 focus:ring-brand-500 @enderror focus:outline-none focus:ring-2 bg-white text-sm"
                                >{{ old('pharmacy_address') }}</textarea>
                                @error('pharmacy_address')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Owner Personal Details --}}
                    <div>
                        <div class="flex items-center gap-3 pb-3 border-b border-slate-200 mb-6">
                            <span class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-700 font-bold text-sm flex items-center justify-center border border-indigo-200">2</span>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">{{ __('Owner / Authorized Pharmacist Information') }}</h2>
                                <p class="text-xs text-slate-500">{{ __('Legal representative contact and identification.') }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-sm">
                            {{-- Owner Name --}}
                            <div>
                                <label for="owner_name" class="block font-semibold text-slate-700 mb-1">{{ __('Owner Full Legal Name') }} <span class="text-red-500">*</span></label>
                                <input 
                                    type="text" 
                                    name="owner_name" 
                                    id="owner_name" 
                                    value="{{ old('owner_name') }}" 
                                    required 
                                    placeholder="e.g. Dr. Farhana Ahmed"
                                    class="w-full px-3.5 py-2.5 rounded-lg border @error('owner_name') border-red-500 focus:ring-red-500 @else border-slate-300 focus:ring-brand-500 @enderror focus:outline-none focus:ring-2 bg-white text-sm"
                                />
                                @error('owner_name')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Owner Email --}}
                            <div>
                                <label for="owner_email" class="block font-semibold text-slate-700 mb-1">{{ __('Owner Email Address') }}</label>
                                <input 
                                    type="email" 
                                    name="owner_email" 
                                    id="owner_email" 
                                    value="{{ old('owner_email') }}" 
                                    placeholder="owner@pharmacy.com"
                                    class="w-full px-3.5 py-2.5 rounded-lg border @error('owner_email') border-red-500 focus:ring-red-500 @else border-slate-300 focus:ring-brand-500 @enderror focus:outline-none focus:ring-2 bg-white text-sm"
                                />
                                <span class="text-[11px] text-slate-400 mt-0.5 block">{{ __('Used for license key delivery and status tracking (optional).') }}</span>
                                @error('owner_email')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Owner Phone --}}
                            <div>
                                <label for="owner_phone" class="block font-semibold text-slate-700 mb-1">{{ __('Owner Mobile Telephone') }} <span class="text-red-500">*</span></label>
                                <input 
                                    type="tel" 
                                    name="owner_phone" 
                                    id="owner_phone" 
                                    value="{{ old('owner_phone') }}" 
                                    required 
                                    placeholder="+8801819001122"
                                    class="w-full px-3.5 py-2.5 rounded-lg border @error('owner_phone') border-red-500 focus:ring-red-500 @else border-slate-300 focus:ring-brand-500 @enderror focus:outline-none focus:ring-2 bg-white text-sm"
                                />
                                @error('owner_phone')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Owner Residential Address --}}
                            <div>
                                <label for="owner_address" class="block font-semibold text-slate-700 mb-1">{{ __('Owner Residential Address') }}</label>
                                <input 
                                    type="text" 
                                    name="owner_address" 
                                    id="owner_address" 
                                    value="{{ old('owner_address') }}" 
                                    placeholder="House 15, Road 27, Banani"
                                    class="w-full px-3.5 py-2.5 rounded-lg border @error('owner_address') border-red-500 focus:ring-red-500 @else border-slate-300 focus:ring-brand-500 @enderror focus:outline-none focus:ring-2 bg-white text-sm"
                                />
                                @error('owner_address')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Step 3: Security & Credentials --}}
                    <div>
                        <div class="flex items-center gap-3 pb-3 border-b border-slate-200 mb-6">
                            <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-sm flex items-center justify-center border border-emerald-200">3</span>
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">{{ __('Security & Portal Access') }}</h2>
                                <p class="text-xs text-slate-500">{{ __('Create a secure password to manage future terminal resets and cloud backups.') }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 text-sm">
                            {{-- Password --}}
                            <div>
                                <label for="password" class="block font-semibold text-slate-700 mb-1">{{ __('Password') }} <span class="text-red-500">*</span></label>
                                <input 
                                    type="password" 
                                    name="password" 
                                    id="password" 
                                    required 
                                    placeholder="••••••••••••"
                                    class="w-full px-3.5 py-2.5 rounded-lg border @error('password') border-red-500 focus:ring-red-500 @else border-slate-300 focus:ring-brand-500 @enderror focus:outline-none focus:ring-2 bg-white text-sm"
                                />
                                <span class="text-[11px] text-slate-400 mt-0.5 block">{{ __('Minimum 8 characters.') }}</span>
                                @error('password')
                                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Confirm Password --}}
                            <div>
                                <label for="password_confirmation" class="block font-semibold text-slate-700 mb-1">{{ __('Confirm Password') }} <span class="text-red-500">*</span></label>
                                <input 
                                    type="password" 
                                    name="password_confirmation" 
                                    id="password_confirmation" 
                                    required 
                                    placeholder="••••••••••••"
                                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-brand-500 focus:outline-none focus:ring-2 bg-white text-sm"
                                />
                            </div>
                        </div>
                    </div>

                    {{-- Submission Notice & Button --}}
                    <div class="pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-xs text-slate-500 max-w-md">
                            {{ __('By clicking submit, you acknowledge that your trade license will undergo compliance verification before your single-device hardware key is activated.') }}
                        </div>
                        <button 
                            type="submit" 
                            class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-sm shadow-md shadow-brand-600/25 transition flex items-center justify-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            {{ __('Submit Registration Application') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
