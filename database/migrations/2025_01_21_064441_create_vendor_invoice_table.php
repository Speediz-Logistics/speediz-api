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
        Schema::create('vendor_invoice', function (Blueprint $table) {
            $table->id();
            //vendor_id
            $table->unsignedBigInteger('vendor_id');
            //invoice_number
            $table->string('invoice_number');
            //total
            $table->decimal('total', 10, 2);
            //Description
            $table->text('description');
            //status
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_invoice');
    }
};
