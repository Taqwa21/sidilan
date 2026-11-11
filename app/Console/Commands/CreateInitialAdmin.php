<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateInitialAdmin extends Command
{
    protected $signature = 'sidilan:buat-admin';

    protected $description = 'Membuat akun Administrator pertama SIDILAN';

    public function handle(): int
    {
        // Perintah hanya boleh dijalankan di server produksi.
        if (!app()->environment('production')) {
            $this->error(
                'Perintah ini hanya boleh dijalankan di lingkungan production.'
            );

            return self::FAILURE;
        }

        $adminRoleId = DB::table('roles')
            ->where('slug', 'admin')
            ->value('id');

        if (!$adminRoleId) {
            $this->error(
                'Role admin belum tersedia. Jalankan seeder terlebih dahulu.'
            );

            return self::FAILURE;
        }

        $adminExists = DB::table('users')
            ->where('role_id', $adminRoleId)
            ->exists();

        if ($adminExists) {
            $this->error(
                'Administrator sudah tersedia. Pembuatan Admin pertama dibatalkan.'
            );

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Nama Administrator'));

        if ($name === '' || mb_strlen($name) > 255) {
            $this->error('Nama wajib diisi dan maksimal 255 karakter.');

            return self::FAILURE;
        }

        $email = strtolower(trim((string) $this->ask('Email Administrator')));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Format email tidak valid.');

            return self::FAILURE;
        }

        if (DB::table('users')->where('email', $email)->exists()) {
            $this->error('Email tersebut sudah digunakan.');

            return self::FAILURE;
        }

        $password = $this->secret('Password (minimal 12 karakter)');
        $confirmation = $this->secret('Konfirmasi password');

        if (!$password || strlen($password) < 12) {
            $this->error('Password minimal harus 12 karakter.');

            return self::FAILURE;
        }

        if ($password !== $confirmation) {
            $this->error('Konfirmasi password tidak cocok.');

            return self::FAILURE;
        }

        DB::table('users')->insert([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role_id' => $adminRoleId,
            'unit_id' => null,
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->newLine();
        $this->info('Administrator pertama SIDILAN berhasil dibuat.');
        $this->line('Email: ' . $email);
        $this->comment('Silakan login menggunakan akun tersebut.');

        return self::SUCCESS;
    }
}
