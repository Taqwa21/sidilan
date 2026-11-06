<?php

namespace Database\Seeders;

use App\Models\DispositionTarget;
use Illuminate\Database\Seeder;

class DispositionTargetSeeder extends Seeder
{
    public function run(): void
    {
        DispositionTarget::updateOrCreate(
            ['code' => 'PLP'],
            ['name' => 'Direktorat Pengelolaan Logistik dan Peralatan']
        );

        DispositionTarget::updateOrCreate(
            ['code' => 'OJLP'],
            ['name' => 'Direktorat Optimasi Jaringan Logistik dan Peralatan']
        );
    }
}
