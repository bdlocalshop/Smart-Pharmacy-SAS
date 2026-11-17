<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

class Pharmacy extends Model
{
    use HasApiTokens, HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pharmacies';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'pharmacy_name',
        'trade_license_number',
        'pharmacy_address',
        'pharmacy_phone',
        'map_location_url',
        'owner_name',
        'owner_address',
        'owner_phone',
        'owner_email',
        'password',
        'verification_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'verification_status' => VerificationStatus::class,
        ];
    }

    /**
     * All licenses associated with this pharmacy.
     */
    public function licenses(): HasMany
    {
        return $this->hasMany(PharmacyLicense::class, 'pharmacy_id');
    }

    /**
     * Get the current active license for the pharmacy.
     */
    public function activeLicense(): HasOne
    {
        return $this->hasOne(PharmacyLicense::class, 'pharmacy_id')
            ->where('is_revoked', false)
            ->where('valid_until', '>=', now())
            ->latestOfMany();
    }

    /**
     * All registered devices for this pharmacy.
     */
    public function registeredDevices(): HasMany
    {
        return $this->hasMany(RegisteredDevice::class, 'pharmacy_id');
    }

    /**
     * All cloud backups uploaded by this pharmacy.
     */
    public function backups(): HasMany
    {
        return $this->hasMany(PharmacyBackup::class, 'pharmacy_id');
    }

    /**
     * Check if the pharmacy is approved.
     */
    public function isApproved(): bool
    {
        return $this->verification_status === VerificationStatus::APPROVED;
    }

    /**
     * Scope a query to only include pharmacies that are active subscribers (approved with a valid, unrevoked license).
     *
     * @param \Illuminate\Database\Eloquent\Builder<\App\Models\Pharmacy> $query
     * @return \Illuminate\Database\Eloquent\Builder<\App\Models\Pharmacy>
     */
    public function scopeActiveSubscriber(Builder $query): Builder
    {
        return $query->where('verification_status', VerificationStatus::APPROVED)
            ->whereHas('licenses', function ($q) {
                $q->where('is_revoked', false)
                    ->where('valid_until', '>=', now());
            });
    }

    /**
     * Extract coordinates from a Google Maps URL, OpenStreetMap URL, or raw lat/long string.
     *
     * @return array{lat: float, lng: float}|null
     */
    public static function extractCoordinates(?string $input): ?array
    {
        if (empty($input)) {
            return null;
        }

        // Matches queries like ?q=23.7925,90.4078, @23.7925,90.4078, or plain "23.7925, 90.4078"
        if (preg_match('/(?:[?&](?:q|query|ll|loc)=|@)?(-?\d{1,2}(?:\.\d+)?)[,\s]+(-?\d{1,3}(?:\.\d+)?)/i', $input, $matches)) {
            $lat = (float) $matches[1];
            $lng = (float) $matches[2];

            if ($lat >= -90.0 && $lat <= 90.0 && $lng >= -180.0 && $lng <= 180.0) {
                return [
                    'lat' => round($lat, 6),
                    'lng' => round($lng, 6),
                ];
            }
        }

        return null;
    }

    /**
     * Get extracted coordinates array [lat, lng] from map_location_url.
     *
     * @return array{lat: float, lng: float}|null
     */
    public function getCoordinatesAttribute(): ?array
    {
        return static::extractCoordinates($this->map_location_url);
    }

    /**
     * Get latitude float.
     */
    public function getLatitudeAttribute(): ?float
    {
        return $this->coordinates['lat'] ?? null;
    }

    /**
     * Get longitude float.
     */
    public function getLongitudeAttribute(): ?float
    {
        return $this->coordinates['lng'] ?? null;
    }

    /**
     * Calculate distance in kilometers to another set of coordinates using Haversine formula.
     */
    public function distanceTo(float $targetLat, float $targetLng): ?float
    {
        $coords = $this->coordinates;
        if (! $coords) {
            return null;
        }

        return static::calculateDistanceKm($coords['lat'], $coords['lng'], $targetLat, $targetLng);
    }

    /**
     * Calculate Great Circle distance between two points in kilometers.
     */
    public static function calculateDistanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c, 1);
    }

    /**
     * Guess or extract the location/city from the pharmacy address.
     */
    public function getEstimatedLocationAttribute(): string
    {
        $address = $this->pharmacy_address;
        $knownCities = [
            'Dhaka', 'Chattogram', 'Chittagong', 'Sylhet', 'Rajshahi', 'Khulna',
            'Barishal', 'Rangpur', 'Mymensingh', 'Cumilla', 'Comilla', 'Gazipur', 'Narayanganj'
        ];

        foreach ($knownCities as $city) {
            if (stripos($address, $city) !== false) {
                return $city === 'Chittagong' ? 'Chattogram' : ($city === 'Comilla' ? 'Cumilla' : $city);
            }
        }

        $parts = array_map('trim', explode(',', $address));
        return count($parts) > 1 ? end($parts) : 'Other';
    }
}
