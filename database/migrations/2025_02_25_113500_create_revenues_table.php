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
        Schema::create('revenues', function (Blueprint $table) {
            $table->id();
            //package id
            $table->unsignedBigInteger('package_id');
            //driver id
            $table->unsignedBigInteger('driver_id');
            //shipment id
            $table->unsignedBigInteger('shipment_id');
            //$deliveryTracking
            $table->unsignedBigInteger('delivery_tracking_id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revenues');
    }
};
