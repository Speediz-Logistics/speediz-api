<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "first_name" => $this->faker->firstName,
            "last_name" => $this->faker->lastName,
            "business_name" => $this->faker->company,
            "business_type" => $this->faker->word,
            "business_description" => $this->faker->sentence,
            "dob" => $this->faker->date(),
            "gender" => $this->faker->randomElement,
            "address" => $this->faker->address,
            'lat' => $this->faker->latitude,
            'lng'   => $this->faker->longitude,
            "contact_number"    => $this->faker->phoneNumber,
            "image" => $this->faker->imageUrl,
            "bank_name" => $this->faker->word,
            "bank_number" => $this->faker->bankAccountNumber,
            "user_id" => User::pluck('id')->random(), // Ensure the user exists
        ];
    }
}
