<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Data Unit Kerja
        $units = [
            [
                'name' => 'Deputi Bidang Logistik dan Peralatan',
                'code' => 'LOGISTIK',
                'description' => 'Unit Kerja Deputi Bidang Logistik dan Peralatan',
            ],
            [
                'name' => 'Direktur',
                'code' => 'PUSAT',
                'description' => 'Unit Kerja Direktur',
            ],
        ];

        foreach ($units as $unit) {
            $existingUnit = DB::table('units')
                ->where('code', $unit['code'])
                ->first();

            if ($existingUnit) {
                DB::table('units')
                    ->where('id', $existingUnit->id)
                    ->update([
                        'name' => $unit['name'],
                        'description' => $unit['description'],
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('units')->insert([
                    ...$unit,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Data Role
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Mengelola seluruh sistem SIDILAN',
            ],
            [
                'name' => 'TU Deputi Logpal',
                'slug' => 'logistik',
                'description' => 'Mengelola surat dan tindak lanjut Deputi Bidang Logistik dan Peralatan',
            ],
            [
                'name' => 'Sekretaris Direktorat Logpal',
                'slug' => 'pusat',
                'description' => 'Mengelola surat dan tindak lanjut Direktur',
            ],
        ];

        foreach ($roles as $role) {
            $existingRole = DB::table('roles')
                ->where('slug', $role['slug'])
                ->first();

            if ($existingRole) {
                DB::table('roles')
                    ->where('id', $existingRole->id)
                    ->update([
                        'name' => $role['name'],
                        'description' => $role['description'],
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('roles')->insert([
                    ...$role,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Data Unit Kerja dan Role SIDILAN berhasil diperiksa.');
    }
}
