<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Branch::insert([
            [
                'name' => 'Buerng Salang',
                'address' => 'Boeung Salang, Toul Kork, Phnom Penh, Cambodia',
                'phone' => '+855 23 999 888',
                'lat' => 11.578707,
                'lng' => 104.892102,
            ],
            [
                'name' => 'Koh Pich',
                'address' => 'Koh Pich, Tonle Bassac, Chamkar Mon, Phnom Penh, Cambodia',
                'phone' => '+855 23 888 777',
                'lat' => 11.558556,
                'lng' => 104.931497,
            ],
            [
                'name' => 'Orussey Market',
                'address' => 'Oknha Tep Phan St. (182), Phnom Penh, Cambodia',
                'phone' => '+855 23 777 666',
                'lat' => 11.562790386757293,
                'lng' => 104.91706988033066,
            ]
        ]);
    }
}
