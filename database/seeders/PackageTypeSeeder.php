<?php

namespace Database\Seeders;

use App\Models\PackageType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PackageTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PackageType::insert([
            [
                'name' => 'Document',
                'description' => 'Document package type',
            ],
            [
                'name' => 'Bottle',
                'description' => 'Bottle package type',
            ],
            [
                'name' => 'Box',
                'description' => 'Box package type',
            ],
            [
                'name' => 'Goods',
                'description' => 'Goods package type',
            ],
            [
                'name' => 'Electronic',
                'description' => 'Electronic package type',
            ],
            //souvenir
            [
                'name' => 'Souvenir',
                'description' => 'Souvenir package type',
            ],
        ]);
    }
}
