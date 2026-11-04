<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRoleId = DB::table('roles')
            ->where('slug', 'admin')
            ->value('id');

        if (!$adminRoleId) {
            throw new \RuntimeException('Role admin belum tersedia.');
        }

        DB::table('users')->updateOrInsert(
            ['email' => 'admin@sidilan.local'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('UMHT2026'),
                'role_id' => $adminRoleId,
                'unit_id' => null,
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}