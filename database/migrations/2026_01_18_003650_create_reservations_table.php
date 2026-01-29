<?php

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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained();
            $table->foreignId('customer_id')->constrained();
            $table->foreignId('location_id')->constrained();
            $table->foreignId('provider_id')->nullable()->constrained('providers');
            $table->datetime('reservation_date');
            $table->integer('day_of_week'); // 1 = Monday, 7 = Sunday
            $table->integer('duration');
            $table->string('status');
            $table->timestamps();

            // Indexes for GetAvailableHoursAction and GetReservationsAction
            $table->index(['location_id', 'reservation_date', 'provider_id']);
            $table->index(['location_id', 'reservation_date']);

            // Index for filtering by status (active reservations)
            $table->index(['location_id', 'reservation_date', 'status']);

            // Index for provider schedule lookups
            $table->index(['provider_id', 'reservation_date']);

            // Index for customer reservation history
            $table->index(['customer_id', 'reservation_date']);

            // Index for organization-wide reporting
            $table->index(['organization_id', 'reservation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
