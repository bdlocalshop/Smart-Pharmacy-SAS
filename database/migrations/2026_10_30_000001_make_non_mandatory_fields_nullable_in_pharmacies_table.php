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
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->text('map_location_url')->nullable()->change();
            $table->text('owner_address')->nullable()->change();
            $table->string('owner_email', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmacies', function (Blueprint $table) {
            $table->text('map_location_url')->nullable(false)->change();
            $table->text('owner_address')->nullable(false)->change();
            $table->string('owner_email', 255)->nullable(false)->change();
        });
    }
};
