<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Data master yang dibutuhkan semua lingkungan.
        $this->call([
            UnitRoleSeeder::class,
            ClassificationSeeder::class,
            DispositionTargetSeeder::class,
        ]);

        // Akun demo hanya dibuat di lingkungan lokal.
        if (app()->environment('local')) {
            $this->call([
                AdminUserSeeder::class,
                StaffUserSeeder::class,
            ]);
        }

        $this->command->info('Seeder SIDILAN selesai dijalankan.');
    }
}
