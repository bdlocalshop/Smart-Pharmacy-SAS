<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\VerificationStatus;
use App\Models\Pharmacy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PharmacyRegistrationController extends Controller
{
    /**
     * Show the pharmacy self-registration onboarding form.
     */
    public function create(): View
    {
        return view('pharmacy.register');
    }

    /**
     * Handle pharmacy registration submission.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // Pharmacy business information
            'pharmacy_name' => ['required', 'string', 'max:255'],
            'trade_license_number' => ['required', 'string', 'max:100', 'unique:pharmacies,trade_license_number'],
            'pharmacy_address' => ['required', 'string'],
            'pharmacy_phone' => ['required', 'string', 'max:50', 'regex:/^(\+?[0-9]{1,4}[\s-]?)?(\(?[0-9]{2,5}\)?[\s-]?)?[0-9]{4,14}$/'],
            'map_location_url' => ['nullable', 'url', 'max:500'],

            // Owner contact credentials
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_address' => ['nullable', 'string'],
            'owner_phone' => ['required', 'string', 'max:50'],
            'owner_email' => ['nullable', 'email', 'max:255', 'unique:pharmacies,owner_email'],

            // Password confirmation
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'trade_license_number.unique' => 'A pharmacy with this Trade License Number is already registered.',
            'owner_email.unique' => 'This owner email address is already registered in our system.',
            'pharmacy_phone.regex' => 'Please enter a valid telephone format with optional country code.',
            'map_location_url.url' => 'Please provide a valid Google Maps or OpenStreetMap pin URL.',
        ]);

        $pharmacy = Pharmacy::create([
            'pharmacy_name' => $validated['pharmacy_name'],
            'trade_license_number' => $validated['trade_license_number'],
            'pharmacy_address' => $validated['pharmacy_address'],
            'pharmacy_phone' => $validated['pharmacy_phone'],
            'map_location_url' => $validated['map_location_url'] ?? null,
            'owner_name' => $validated['owner_name'],
            'owner_address' => $validated['owner_address'] ?? null,
            'owner_phone' => $validated['owner_phone'],
            'owner_email' => $validated['owner_email'] ?? null,
            'password' => Hash::make($validated['password']),
            'verification_status' => VerificationStatus::PENDING,
        ]);

        return redirect()
            ->route('pharmacy.status', ['ref' => $pharmacy->id])
            ->with('registration_success', true)
            ->with('registered_ref', $pharmacy->id)
            ->with('pharmacy_name', $pharmacy->pharmacy_name);
    }

    /**
     * Check onboarding application verification status.
     */
    public function status(Request $request): View
    {
        $pharmacy = null;
        $query = trim((string) $request->input('ref') ?: (string) $request->input('query'));

        if (! empty($query)) {
            $pharmacy = Pharmacy::with(['activeLicense', 'registeredDevices'])
                ->where('id', $query)
                ->orWhere('trade_license_number', $query)
                ->orWhere('owner_email', $query)
                ->first();
        }

        return view('pharmacy.status', compact('pharmacy', 'query'));
    }
}
