<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Tabel unit kerja di bawah masing-masing instansi.
         */
        Schema::create('work_units', function (Blueprint $table) {
            $table->id();

            $table->foreignId('unit_id')
                ->constrained('units')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('code', 50);
            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(['unit_id', 'code']);
        });

        /*
         * Menghubungkan akun pengguna dengan unit kerja.
         * Nullable agar akun lama tetap dapat digunakan.
         */
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('work_unit_id')
                ->nullable()
                ->after('unit_id')
                ->constrained('work_units')
                ->nullOnDelete();
        });

        /*
         * Menghubungkan tindak lanjut dengan unit kerja.
         * Data tindak lanjut lama tidak dipaksakan masuk
         * ke PLP atau OJLP.
         */
        Schema::table('letter_follow_ups', function (Blueprint $table) {
            $table->foreignId('work_unit_id')
                ->nullable()
                ->after('user_id')
                ->constrained('work_units')
                ->nullOnDelete();
        });

        /*
         * Satu disposisi dapat ditujukan kepada beberapa
         * unit kerja sekaligus.
         */
        Schema::create('disposition_work_unit', function (Blueprint $table) {
            $table->id();

            $table->foreignId('letter_disposition_id')
                ->constrained('letter_dispositions')
                ->cascadeOnDelete();

            $table->foreignId('work_unit_id')
                ->constrained('work_units')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique([
                'letter_disposition_id',
                'work_unit_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disposition_work_unit');

        Schema::table('letter_follow_ups', function (Blueprint $table) {
            $table->dropForeign(['work_unit_id']);
            $table->dropColumn('work_unit_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['work_unit_id']);
            $table->dropColumn('work_unit_id');
        });

        Schema::dropIfExists('work_units');
    }
};
