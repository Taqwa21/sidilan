<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_histories', function (Blueprint $table) {
            $table->id();

            // Surat yang mengalami perubahan
            $table->foreignId('letter_id')
                ->constrained('letters')
                ->cascadeOnDelete();

            // Status sebelum dan sesudah perubahan
            $table->string('old_status')->nullable();
            $table->string('new_status');

            // Keterangan tindak lanjut
            $table->text('description')->nullable();

            // Petugas yang melakukan perubahan
            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_histories');
    }
};