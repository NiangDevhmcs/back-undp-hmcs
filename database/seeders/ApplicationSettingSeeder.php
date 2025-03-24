<?php

namespace Database\Seeders;

use App\Models\ApplicationSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApplicationSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ApplicationSetting::create([
            'logo' => null,
            'name' => 'UNDP',
            'address' => 'Main Street, City, Country',
            'email' => 'contact@yourcompany.com',
            'phone_one' => '780987654',
            'phone_two' => '780987654',
            'slogan'=>'UNDP'
        ]);
    }
}
