<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Roleseeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    // Liste des rôles à insérer avec des IDs fixes
    DB::table('roles')->insert([
        [
            'id' => 1,
            'name' => 'Sysmanager',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'id' => 2,
            'name' => 'Admin',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'id' => 3,
            'name' => 'Viewer',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'id' => 4,
            'name' => 'Editor',
            'created_at' => now(),
            'updated_at' => now()
        ],
        [
            'id' => 5,
            'name' => 'Support',
            'created_at' => now(),
            'updated_at' => now()
        ]
    ]);
}
}
