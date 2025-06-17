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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name'); // User's first name
            $table->string('last_name'); // User's last name
            $table->string("driver_type")->nullable();
            $table->string("driver_description")->nullable();
            $table->date('dob')->nullable(); // Optional date of birth
            $table->string('gender', 10)->nullable(); // Gender (optional, e.g., Male/Female/Other)
            $table->text('zone')->nullable(); // Full address (optional)
            $table->string('contact_number', 20)->nullable(); // Contact number (optional)
            $table->string('telegram_contact')->nullable();
            $table->string('image')->nullable(); // Optional profile image
            $table->string("bank_name")->nullable();
            $table->string("bank_number")->nullable();
            $table->string("cv")->nullable();
            $table->string('address')->nullable();
            $table->string("user_id");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
