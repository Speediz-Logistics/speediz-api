<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'number' => $this->faker->unique()->randomNumber(8),
            'name' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'price' => $this->faker->randomFloat(2, 0, 1000),
            'description' => $this->faker->sentence(),
            'image' => $this->faker->imageUrl(),
            'zone' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
            'picked_up_at' => null,
            'delivered_at' => null,
            'vendor_id' => 1,
            'customer_id' => 1,
            'location_id' => 1,
            'driver_id' => 1,
            'shipment_id' => 1,
            'invoice_id' => 1,
            'status' => $this->faker->randomElement(['completed', 'pending', 'in_transit', 'cancelled']),
            'created_at' => $this->faker->dateTimeBetween('-12 months', 'now'),
        ];
    }
}
