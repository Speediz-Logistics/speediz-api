<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Branch;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Default Branch', // This will be overridden in the seeder
            'address' => 'Default Address',
            'phone' => '+855 00 000 000',
            'lat' => 0.000000,
            'lng' => 0.000000,
        ];
    }
}
