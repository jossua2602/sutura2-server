<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         \App\Models\User::create([
        'name' => 'Platform Admin',
        'email' => 'admin@sutura.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
        'role' => 'admin',
    ]);

    \App\Models\SubscriptionPlan::insert([
        ['plan_name' => 'Basic',   'price' => 149, 'max_staff' => 2,  'max_branches' => 1, 'created_at' => now(), 'updated_at' => now()],
        ['plan_name' => 'Pro',     'price' => 599, 'max_staff' => 5,  'max_branches' => 2, 'created_at' => now(), 'updated_at' => now()],
        ['plan_name' => 'Premium', 'price' => 899, 'max_staff' => 15, 'max_branches' => 5, 'created_at' => now(), 'updated_at' => now()],
    ]);
    }
}
