<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Constants\ConstUserRole;
use App\Models\User;
use App\Models\Admin;
use App\Models\Vendor;
use App\Models\Employee;
use App\Models\Driver;
use Carbon\Carbon;

class UserSeeder extends Seeder
{

    public function run()
    {
        $image1 = 'https://i.pravatar.cc/300?img=1';
        $image2 = 'https://i.pravatar.cc/300?img=2';
        $image3 = 'https://i.pravatar.cc/300?img=3';
        $image4 = 'https://i.pravatar.cc/300?img=4';
        $businessImage = 'https://dummyimage.com/300/09f/fff.png';

        /** -------------------------------------------------
         *  ADMIN USER
         *  ------------------------------------------------- */
        $adminUser = User::create([
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => ConstUserRole::ADMIN,
            'email_verified_at' => Carbon::now(),
        ]);

        Admin::create([
            'name' => 'Main Admin',
            'phone' => '0123456789',
            'username' => 'admin',
            'image' => $image1,
            'user_id' => $adminUser->id,
        ]);


        /** -------------------------------------------------
         *  EMPLOYEE USER
         *  ------------------------------------------------- */
        $employeeUser = User::create([
            'email' => 'employee@gmail.com',
            'password' => Hash::make('password'),
            'role' => ConstUserRole::EMPLOYEE,
            'email_verified_at' => Carbon::now(),
        ]);

        Employee::create([
            'first_name' => 'Yong',
            'last_name' => 'Employee',
            'contact_number' => '099887766',
            'image' => $image2,
            'user_id' => $employeeUser->id,
        ]);



        /** -------------------------------------------------
         *  VENDOR USER
         *  ------------------------------------------------- */
        $vendorUser = User::create([
            'email' => 'vendor@gmail.com',
            'password' => Hash::make('password'),
            'role' => ConstUserRole::VENDOR,
            'email_verified_at' => Carbon::now(),
        ]);

        Vendor::create([
            'first_name' => 'Yong',
            'last_name' => 'Vendor',
            'business_name' => 'Yong Electronics',
            'business_type' => 'Electronics',
            'business_description' => 'Selling digital products',
            'dob' => '1995-02-05',
            'gender' => 'male',
            'address' => 'Phnom Penh',
            'lat' => 11.565512262856975,
            'lng' => 104.89838885047813,
            'contact_number' => '098765432',
            'image' => $image3,
            'bank_name' => 'ABA',
            'bank_number' => '000123456789',
            'user_id' => $vendorUser->id,
        ]);



        /** -------------------------------------------------
         *  DELIVERY / DRIVER USER
         *  ------------------------------------------------- */
        $driverUser = User::create([
            'email' => 'driver@gmail.com',
            'password' => Hash::make('password'),
            'role' => ConstUserRole::DELIVERY,
            'email_verified_at' => Carbon::now(),
        ]);

        Driver::create([
            'first_name' => 'Rith',
            'last_name' => 'Driver',
            'driver_type' => 'motor',
            'driver_description' => 'Fast delivery driver',
            'dob' => '1998-03-10',
            'gender' => 'male',
            'zone' => 'Toul Kork',
            'contact_number' => '097112233',
            'telegram_contact' => '@rithdriver',
            'image' => $image4,
            'bank_name' => 'ACLEDA',
            'bank_number' => '123987456',
            'cv' => null,
            'address' => 'Phnom Penh',
            'user_id' => $driverUser->id,
        ]);

    }
}
