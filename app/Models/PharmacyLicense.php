<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LicenseStatus;
use App\Enums\PlatformType;
use App\Enums\VerificationStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Crypt;

class PharmacyLicense extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pharmacy_licenses';

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
        'pharmacy_id',
        'license_key',
        'client_secret_hash',
        'session_days_limit',
        'valid_from',
        'valid_until',
        'bound_device_uuid',
        'bound_device_name',
        'bound_platform',
        'last_validated_at',
        'is_revoked',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'client_secret_hash',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'session_days_limit' => 'integer',
            'valid_from' => 'datetime',
            'valid_until' => 'datetime',
            'last_validated_at' => 'datetime',
            'bound_platform' => PlatformType::class,
            'is_revoked' => 'boolean',
        ];
    }

    /**
     * Pharmacy owning this license.
     */
    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'pharmacy_id');
    }

    /**
     * Registered devices linked to this license.
     */
    public function registeredDevices(): HasMany
    {
        return $this->hasMany(RegisteredDevice::class, 'pharmacy_license_id');
    }

    /**
     * Active bound device record.
     */
    public function boundDevice(): HasOne
    {
        return $this->hasOne(RegisteredDevice::class, 'pharmacy_license_id')
            ->where('is_active', true);
    }

    /**
     * Get decrypted shared secret or fallback to stored value.
     */
    public function getPlainSecret(): string
    {
        if (empty($this->client_secret_hash)) {
            return '';
        }

        try {
            return Crypt::decryptString($this->client_secret_hash);
        } catch (\Throwable) {
            return $this->client_secret_hash;
        }
    }

    /**
     * Remaining session days until expiration.
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->is_revoked || ! $this->valid_until) {
            return 0;
        }

        $now = Carbon::now();
        if ($now->greaterThanOrEqualTo($this->valid_until)) {
            return 0;
        }

        return (int) $now->diffInDays($this->valid_until, false);
    }

    /**
     * Determine whether license is currently active and valid.
     */
    public function isValid(): bool
    {
        return ! $this->is_revoked &&
            $this->valid_until->isFuture() &&
            $this->pharmacy?->verification_status === VerificationStatus::APPROVED;
    }

    /**
     * Return calculated license status enum.
     */
    public function getStatusAttribute(): LicenseStatus
    {
        if ($this->is_revoked) {
            return LicenseStatus::REVOKED;
        }

        if ($this->pharmacy && $this->pharmacy->verification_status === VerificationStatus::SUSPENDED) {
            return LicenseStatus::SUSPENDED;
        }

        if ($this->valid_until && Carbon::now()->greaterThanOrEqualTo($this->valid_until)) {
            return LicenseStatus::EXPIRED;
        }

        return LicenseStatus::ACTIVE;
    }
}
