<?php

namespace Database\Seeders;

use App\Models\Restaurant;
use App\Models\Staff;
use Illuminate\Database\Seeder;

class StaffSeeder extends Seeder
{
    public function run(): void
    {
        $r1 = Restaurant::create(['name' => 'Restaurant 1', 'owner_phone' => '1234567890']);
        $r2 = Restaurant::create(['name' => 'Restaurant 2', 'owner_phone' => '0987654321']);

        foreach ([[$r1, 'owner1@test.com'], [$r2, 'owner2@test.com']] as [$r, $email]) {
            Staff::create([
                'restaurant_id' => $r->id,
                'name' => 'Owner',
                'role' => 'owner',
                'phone' => '0100000000',
                'email' => $email,
                'password_hash' => '12341234',
                'is_active' => true,
            ]);
        }

        Staff::create([
            'restaurant_id' => $r1->id,
            'name' => 'Cashier',
            'role' => 'cashier',
            'phone' => '0101111111',
            'email' => 'cashier@test.com',
            'password_hash' => '12345678',
            'is_active' => true,
        ]);
    }
}
