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
        Schema::create('pharmacies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('pharmacy_name', 255);
            $table->string('trade_license_number', 100)->unique();
            $table->text('pharmacy_address');
            $table->string('pharmacy_phone', 50);
            $table->text('map_location_url')->nullable();
            $table->string('owner_name', 255);
            $table->text('owner_address')->nullable();
            $table->string('owner_phone', 50);
            $table->string('owner_email', 255)->nullable()->unique();
            $table->string('password', 255);
            $table->string('verification_status', 30)->default('pending')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacies');
    }
};
