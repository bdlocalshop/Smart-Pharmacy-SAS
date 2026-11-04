<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pharmacy_licenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('pharmacy_id')
                ->constrained('pharmacies')
                ->cascadeOnDelete();
            $table->string('license_key', 64)->unique();
            $table->text('client_secret_hash');
            $table->unsignedInteger('session_days_limit')->default(30);
            $table->dateTime('valid_from');
            $table->dateTime('valid_until');
            $table->string('bound_device_uuid', 255)->nullable()->index();
            $table->string('bound_device_name', 150)->nullable();
            $table->string('bound_platform', 50)->nullable();
            $table->timestamp('last_validated_at')->nullable();
            $table->boolean('is_revoked')->default(false)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_licenses');
    }
};
