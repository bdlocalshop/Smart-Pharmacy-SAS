<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ItemType;
use App\Models\CatalogItem;
use App\Models\Pharmacy;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    /**
     * Show the public storefront & catalog showcase.
     */
    public function index(): View
    {
        $items = CatalogItem::active()
            ->orderBy('item_type')
            ->orderBy('title')
            ->get();

        $productsCount = $items->where('item_type', ItemType::PRODUCT)->count();
        $servicesCount = $items->where('item_type', ItemType::SERVICE)->count();

        $activePharmacies = Pharmacy::activeSubscriber()
            ->with('activeLicense')
            ->orderBy('pharmacy_name')
            ->get();

        $pharmacyLocations = $activePharmacies->map(fn (Pharmacy $p) => $p->estimated_location)
            ->unique()
            ->filter()
            ->sort()
            ->values()
            ->all();

        return view('home', compact(
            'items',
            'productsCount',
            'servicesCount',
            'activePharmacies',
            'pharmacyLocations'
        ));
    }
}
