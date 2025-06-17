<?php

use App\Constants\ConstVendorRequestStatus;
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
        Schema::create('request_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('first_name'); // User's first name
            $table->string('last_name'); // User's last name
            $table->string('email')->unique();
            $table->string('business_name')->nullable(); // Optional business name
            $table->string("business_type")->nullable();
            $table->string("business_description")->nullable();
            $table->date('dob')->nullable(); // Optional date of birth
            $table->string('gender', 10)->nullable(); // Gender (optional, e.g., Male/Female/Other)
            $table->text('address')->nullable(); // Full address (optional)
            //latitude and longitude
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('contact_number', 20)->nullable(); // Contact number (optional)
            $table->string('image')->nullable(); // Optional profile image
            $table->string("bank_name")->nullable();
            $table->string("bank_number")->nullable();
            $table->string("status")->default(ConstVendorRequestStatus::Pending);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_vendors');
    }
};
