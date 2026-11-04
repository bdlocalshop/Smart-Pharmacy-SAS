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
        $query = Pharmacy::activeSubscriber()
            ->with('activeLicense')
            ->orderBy('pharmacy_name');

        if ($request->filled('search')) {
            $term = '%' . trim((string) $request->input('search')) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('pharmacy_name', 'like', $term)
                    ->orWhere('pharmacy_address', 'like', $term)
                    ->orWhere('pharmacy_phone', 'like', $term);
            });
        }

        if ($request->filled('location') && $request->input('location') !== 'all') {
            $loc = '%' . trim((string) $request->input('location')) . '%';
            $query->where('pharmacy_address', 'like', $loc);
        }

        $pharmacies = $query->get();

        // Extract available unique locations from all active subscribers
        $allActive = Pharmacy::activeSubscriber()->get();
        $locations = $allActive->map(fn (Pharmacy $p) => $p->estimated_location)
            ->unique()
            ->filter()
            ->sort()
            ->values()
            ->all();

        return view('pharmacy.directory', [
            'pharmacies' => $pharmacies,
            'locations' => $locations,
            'totalCount' => $allActive->count(),
            'initialSearch' => (string) $request->input('search', ''),
            'initialLocation' => (string) $request->input('location', 'all'),
        ]);
    }
}
