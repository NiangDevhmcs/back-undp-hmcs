<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Sysmanager;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class Userseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systemManageRole = Role::firstOrCreate(['name' => 'Sysmanager']);

        $user = User::create([
            'first_name' => 'Ibrahima',
            'last_name' => 'NIANG',
            'email' => 'unpd@gmail.com',
            'phone_number_one' => '770906538',
            'status' => true,
            'address' => 'Bargny',
            'role_id' => $systemManageRole->id,
            'gender'=>'male',
            'password' => Hash::make('password'),
        ]);

        Sysmanager::create([
            'user_id' => $user->id
        ]);
    }
}
