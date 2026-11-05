<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassificationSeeder extends Seeder
{
    public function run(): void
    {
        $classifications = [
            [
                'name' => 'Umum',
                'code' => 'UMUM',
                'description' => 'Surat dengan klasifikasi umum.',
            ],
            [
                'name' => 'Penting',
                'code' => 'PENTING',
                'description' => 'Surat yang memerlukan perhatian khusus.',
            ],
            [
                'name' => 'Segera',
                'code' => 'SEGERA',
                'description' => 'Surat yang harus segera ditindaklanjuti.',
            ],
            [
                'name' => 'Rahasia',
                'code' => 'RAHASIA',
                'description' => 'Surat yang bersifat rahasia.',
            ],
        ];

        foreach ($classifications as $classification) {
            $exists = DB::table('classifications')
                ->where('code', $classification['code'])
                ->exists();

            if (!$exists) {
                DB::table('classifications')->insert([
                    ...$classification,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Pemeriksaan klasifikasi surat selesai.');
    }
}
