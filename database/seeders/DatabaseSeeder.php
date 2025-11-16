<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
//         \App\Models\User::factory(1)->create();
//        $user =
//            new \App\Models\User();
//        $user->name = 'admin';
//        $user->email = 'admin@gmail.com';
//        $user->password = bcrypt('admin123');
//        $user->save();

        $this->call(UserSeeder::class);
        $this->call(BranchSeeder::class);
        $this->call(PackageTypeSeeder::class);
    }
}
