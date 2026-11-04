<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->foreignId('sender_unit_id')
                ->nullable()
                ->after('origin')
                ->constrained('units')
                ->nullOnDelete();

            $table->foreignId('receiver_unit_id')
                ->nullable()
                ->after('sender_unit_id')
                ->constrained('units')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('letters', function (Blueprint $table) {
            $table->dropForeign(['sender_unit_id']);
            $table->dropForeign(['receiver_unit_id']);

            $table->dropColumn([
                'sender_unit_id',
                'receiver_unit_id'
            ]);
        });
    }
};