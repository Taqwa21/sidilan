<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Seeder akun demo hanya boleh dijalankan di lokal.
        if (!app()->environment('local')) {
            throw new \RuntimeException(
                'AdminUserSeeder hanya boleh dijalankan di lingkungan lokal.'
            );
        }

        $adminRoleId = DB::table('roles')
            ->where('slug', 'admin')
            ->value('id');

        if (!$adminRoleId) {
            throw new \RuntimeException('Role admin belum tersedia.');
        }

        $email = 'admin@sidilan.local';

        DB::table('users')->updateOrInsert(
            ['email' => $email],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role_id' => $adminRoleId,
                'unit_id' => null,
                'email_verified_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        $this->command->info(
            'Akun administrator lokal berhasil dibuat atau diperbarui.'
        );
    }
}
