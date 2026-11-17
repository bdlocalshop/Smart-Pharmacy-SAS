<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PharmacyDirectoryController;
use App\Http\Controllers\PharmacyRegistrationController;
use Illuminate\Support\Facades\Route;

// Active subscriber pharmacies directory (Default Home Page)
Route::get('/', [PharmacyDirectoryController::class, 'index'])->name('pharmacy.directory');
Route::get('/pharmacies', [PharmacyDirectoryController::class, 'index']); // Alias support

// Public storefront showcase & catalog
Route::get('/products', [HomeController::class, 'index'])->name('products');

// Pharmacy self-registration portal
Route::get('/register-pharmacy', [PharmacyRegistrationController::class, 'create'])->name('pharmacy.register');
Route::post('/register-pharmacy', [PharmacyRegistrationController::class, 'store'])->name('pharmacy.register.store');

// Registration status tracker
Route::get('/status', [PharmacyRegistrationController::class, 'status'])->name('pharmacy.status');

// Redirect aliases for developer documentation gated behind Filament admin panel
Route::get('/admin/docs', fn () => redirect()->to('/admin/api-docs'));
Route::get('/admin/api-reference', fn () => redirect()->to('/admin/api-docs'));

// Gated standalone Scalar OpenAPI document view
Route::middleware(['web', 'auth'])->get('/admin/api-docs/embed', function () {
    $page = new \App\Filament\Pages\ApiDocsPage();
    return view('filament.pages.scalar-embed', [
        'spec' => $page->getOpenApiSpec(),
    ]);
})->name('admin.api-docs.embed');
