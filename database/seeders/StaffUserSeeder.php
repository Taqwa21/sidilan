<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StaffUserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Petugas Logistik',
                'email' => 'logistik@sidilan.local',
                'role' => 'logistik',
                'unit' => 'LOGISTIK',
            ],
            [
                'name' => 'Petugas Pusat',
                'email' => 'pusat@sidilan.local',
                'role' => 'pusat',
                'unit' => 'PUSAT',
            ],
        ];

        foreach ($accounts as $account) {
            $roleId = DB::table('roles')
                ->where('slug', $account['role'])
                ->value('id');

            $unitId = DB::table('units')
                ->where('code', $account['unit'])
                ->value('id');

            if (!$roleId || !$unitId) {
                throw new \RuntimeException(
                    'Role atau unit tidak ditemukan untuk ' . $account['name']
                );
            }

            DB::table('users')->updateOrInsert(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make('Sidilan2026!'),
                    'role_id' => $roleId,
                    'unit_id' => $unitId,
                    'email_verified_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
