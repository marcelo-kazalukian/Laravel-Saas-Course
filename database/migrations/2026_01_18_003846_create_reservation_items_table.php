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
        Schema::create('reservation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained();
            $table->foreignId('reservation_id')->constrained();
            $table->foreignId('service_id')->constrained();
            $table->foreignId('service_option_id')->constrained();
            $table->string('service_name');
            $table->string('service_option_name');
            $table->integer('price'); // price in cents
            $table->integer('duration'); // duration in minutes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_items');
    }
};
