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
                'password' => 'Sidilan2026!',
                'role' => 'logistik',
                'unit' => 'LOGISTIK',
            ],
            [
                'name' => 'Petugas Pusat',
                'email' => 'pusat@sidilan.local',
                'password' => 'Sidilan2026!',
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
                    "Role atau unit untuk {$account['email']} belum tersedia."
                );
            }

            $exists = DB::table('users')
                ->where('email', $account['email'])
                ->exists();

            if ($exists) {
                $this->command->info(
                    "Akun {$account['email']} sudah tersedia. Tidak ada perubahan."
                );

                continue;
            }

            DB::table('users')->insert([
                'name' => $account['name'],
                'email' => $account['email'],
                'password' => Hash::make($account['password']),
                'role_id' => $roleId,
                'unit_id' => $unitId,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->info(
                "Akun {$account['email']} berhasil dibuat."
            );
        }
    }
}
