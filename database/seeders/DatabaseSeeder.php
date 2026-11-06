<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UnitRoleSeeder::class,
            ClassificationSeeder::class,
            AdminUserSeeder::class,
            StaffUserSeeder::class,
            DispositionTargetSeeder::class,
        ]);
    }
}
