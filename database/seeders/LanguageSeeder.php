<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get('database/data/langues.json');
        $data = json_decode($json, true);

        foreach ($data as $value) {
            // Vérifie si la langue existe déjà en se basant sur le code
            Language::firstOrCreate(
                ['code' => $value['code']], // Clé unique pour vérifier l'existence
                [
                    'name' => $value['name'],
                    'flag' => $value['flag']
                ]
            );
        }
    }
}
