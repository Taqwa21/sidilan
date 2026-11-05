<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitRoleSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            [
                'name' => 'BNPB Logistik',
                'code' => 'LOGISTIK',
                'description' => 'Unit BNPB Logistik',
            ],
            [
                'name' => 'BNPB Pusat',
                'code' => 'PUSAT',
                'description' => 'Unit BNPB Pusat',
            ],
        ];

        foreach ($units as $unit) {
            $exists = DB::table('units')
                ->where('code', $unit['code'])
                ->exists();

            if (!$exists) {
                DB::table('units')->insert([
                    ...$unit,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Mengelola seluruh sistem',
            ],
            [
                'name' => 'Logistik',
                'slug' => 'logistik',
                'description' => 'Petugas BNPB Logistik',
            ],
            [
                'name' => 'Pusat',
                'slug' => 'pusat',
                'description' => 'Petugas BNPB Pusat',
            ],
        ];

        foreach ($roles as $role) {
            $exists = DB::table('roles')
                ->where('slug', $role['slug'])
                ->exists();

            if (!$exists) {
                DB::table('roles')->insert([
                    ...$role,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Pemeriksaan unit dan role selesai.');
    }
}
