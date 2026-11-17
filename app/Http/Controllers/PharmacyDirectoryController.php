<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PharmacyDirectoryController extends Controller
{
    /**
     * Display the directory of active subscriber pharmacies.
     */
    public function index(Request $request): View
    {
        $allActive = Pharmacy::activeSubscriber()
            ->with('activeLicense')
            ->orderBy('pharmacy_name')
            ->get();

        $searchRaw = trim((string) $request->input('search', ''));
        $searchCoords = Pharmacy::extractCoordinates($searchRaw);
        $selectedLocation = strtolower(trim((string) $request->input('location', 'all')));
        $distanceKm = (float) $request->input('distance', 25.0);
        if ($distanceKm <= 0) {
            $distanceKm = 25.0;
        }

        $filteredPharmacies = $allActive;

        // Apply location filter if specified
        if ($selectedLocation !== 'all' && $selectedLocation !== '') {
            $filteredPharmacies = $filteredPharmacies->filter(function (Pharmacy $p) use ($selectedLocation) {
                return str_contains(strtolower($p->pharmacy_address), $selectedLocation)
                    || strtolower($p->estimated_location) === $selectedLocation;
            });
        }

        $noMatchWithinRadius = false;
        $closestFallbacks = collect();

        // Check if searching via coordinates / map link
        if ($searchCoords !== null) {
            // Proximity mode: calculate distance for each pharmacy
            $withDistances = $filteredPharmacies->map(function (Pharmacy $p) use ($searchCoords) {
                $p->calculated_distance = ($p->latitude !== null && $p->longitude !== null)
                    ? Pharmacy::calculateDistanceKm($p->latitude, $p->longitude, $searchCoords['lat'], $searchCoords['lng'])
                    : null;
                return $p;
            })->sortBy(fn (Pharmacy $p) => $p->calculated_distance ?? 999999)->values();

            // Filter within selected radius
            $inRadius = $withDistances->filter(function (Pharmacy $p) use ($distanceKm) {
                return $p->calculated_distance !== null && $p->calculated_distance <= $distanceKm;
            })->values();

            if ($inRadius->isEmpty() && $withDistances->isNotEmpty()) {
                $noMatchWithinRadius = true;
                // Provide the closest pharmacies beneath the notice
                $closestFallbacks = $withDistances->take(3)->values();
                $filteredPharmacies = collect();
            } else {
                $filteredPharmacies = $inRadius;
            }
        } elseif ($searchRaw !== '') {
            // Standard text search: name, address, phone, or map link
            $term = strtolower($searchRaw);
            $filteredPharmacies = $filteredPharmacies->filter(function (Pharmacy $p) use ($term) {
                return str_contains(strtolower($p->pharmacy_name), $term)
                    || str_contains(strtolower($p->pharmacy_address), $term)
                    || str_contains(strtolower($p->pharmacy_phone), $term)
                    || ($p->map_location_url && str_contains(strtolower($p->map_location_url), $term));
            })->values();
        }

        // Available unique locations for dropdown/quick filters
        $locations = $allActive->map(fn (Pharmacy $p) => $p->estimated_location)
            ->unique()
            ->filter()
            ->sort()
            ->values()
            ->all();

        // Format pharmacies as rich JSON for client-side Alpine.js reactivity
        $pharmaciesToSerialize = ($searchRaw !== '' || ($selectedLocation !== 'all' && $selectedLocation !== ''))
            ? ($noMatchWithinRadius ? $closestFallbacks : $filteredPharmacies)
            : $allActive;

        $pharmaciesJson = $pharmaciesToSerialize->map(function (Pharmacy $p) {
            return [
                'id' => $p->id,
                'name' => $p->pharmacy_name,
                'trade_license' => $p->trade_license_number,
                'address' => $p->pharmacy_address,
                'phone' => $p->pharmacy_phone,
                'location' => $p->estimated_location,
                'map_url' => $p->map_location_url ?: 'https://www.google.com/maps/search/?api=1&query=' . urlencode($p->pharmacy_name . ' ' . $p->pharmacy_address),
                'lat' => $p->latitude,
                'lng' => $p->longitude,
            ];
        })->values()->all();

        return view('pharmacy.directory', [
            'pharmacies' => $filteredPharmacies,
            'allPharmacies' => $allActive,
            'pharmaciesJson' => $pharmaciesJson,
            'locations' => $locations,
            'totalCount' => $allActive->count(),
            'initialSearch' => $searchRaw,
            'initialLocation' => $selectedLocation,
            'initialDistance' => $distanceKm,
            'initialCoords' => $searchCoords,
            'noMatchWithinRadius' => $noMatchWithinRadius,
            'closestFallbacks' => $closestFallbacks,
        ]);
    }
}
