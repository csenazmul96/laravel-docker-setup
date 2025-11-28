<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\UserPermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('customers')->insert([
            'first_name' => 'Ltnkto',
            'last_name' => 'Ltd.',
            'email' => 'customer@lynkto.com',
            'customer_uid' => uuid_create(),
            'password' => Hash::make(123456),
            'company_name' => 'Lynkto',
            'primary_customer_market' => 1,
            'sell_online' => 1,
            'seller_permit_number' => '123456',
            'active' => 1,
            'verified' => 1,
            'block' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        DB::table('admins')->insert([
            'name' => 'Super Admin',
            'email' => 'admin@lynkto.com',
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'password' => Hash::make(123456),
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
