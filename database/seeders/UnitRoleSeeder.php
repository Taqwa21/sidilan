<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Data unit kerja
        DB::table('units')->updateOrInsert(
            ['code' => 'LOGISTIK'],
            [
                'name' => 'BNPB Logistik',
                'description' => 'Unit BNPB Logistik',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('units')->updateOrInsert(
            ['code' => 'PUSAT'],
            [
                'name' => 'BNPB Pusat',
                'description' => 'Unit BNPB Pusat',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        // Data role pengguna
        DB::table('roles')->updateOrInsert(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Mengelola seluruh aplikasi',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('roles')->updateOrInsert(
            ['slug' => 'logistik'],
            [
                'name' => 'Logistik',
                'description' => 'Mengelola dan memantau surat dari BNPB Logistik',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        DB::table('roles')->updateOrInsert(
            ['slug' => 'pusat'],
            [
                'name' => 'Pusat',
                'description' => 'Mengelola penerimaan, disposisi, dan tindak lanjut surat',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }
}