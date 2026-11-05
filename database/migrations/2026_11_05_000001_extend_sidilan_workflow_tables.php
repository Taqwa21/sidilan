<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. KLASIFIKASI SURAT
        |--------------------------------------------------------------------------
        */

        Schema::create('classifications', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | 2. PENGEMBANGAN TABEL LETTERS
        |--------------------------------------------------------------------------
        */

        Schema::table('letters', function (Blueprint $table) {
            $table->foreignId('classification_id')
                ->nullable()
                ->constrained('classifications')
                ->nullOnDelete();

            $table->foreignId('sent_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('sent_at')->nullable();

            $table->foreignId('received_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('received_at')->nullable();

            $table->text('received_note')->nullable();

            $table->index(
                ['sender_unit_id', 'status'],
                'letters_sender_status_index'
            );

            $table->index(
                ['receiver_unit_id', 'status'],
                'letters_receiver_status_index'
            );

            $table->index('letter_date');
        });

        /*
        |--------------------------------------------------------------------------
        | 3. DISPOSISI SURAT
        |--------------------------------------------------------------------------
        */

        Schema::create('letter_dispositions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('letter_id')
                ->constrained('letters')
                ->cascadeOnDelete();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('assigned_to_unit_id')
                ->nullable()
                ->constrained('units')
                ->nullOnDelete();

            $table->foreignId('assigned_to_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('instruction');

            $table->string('status')->default('aktif');

            $table->timestamp('disposed_at');

            $table->timestamps();

            $table->index(['letter_id', 'status']);
        });

        /*
        |--------------------------------------------------------------------------
        | 4. TINDAK LANJUT SURAT
        |--------------------------------------------------------------------------
        */

        Schema::create('letter_follow_ups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('letter_id')
                ->constrained('letters')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('progress_status')
                ->default('dalam_proses');

            $table->text('description');

            $table->timestamps();

            $table->index(['letter_id', 'created_at']);
        });

        /*
        |--------------------------------------------------------------------------
        | 5. KOMENTAR SURAT
        |--------------------------------------------------------------------------
        */

        Schema::create('letter_comments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('letter_id')
                ->constrained('letters')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('comment');

            $table->timestamps();

            $table->index(['letter_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_comments');

        Schema::dropIfExists('letter_follow_ups');

        Schema::dropIfExists('letter_dispositions');

        Schema::table('letters', function (Blueprint $table) {
            $table->dropIndex('letters_sender_status_index');
            $table->dropIndex('letters_receiver_status_index');
            $table->dropIndex(['letter_date']);

            $table->dropConstrainedForeignId('classification_id');
            $table->dropConstrainedForeignId('sent_by');
            $table->dropConstrainedForeignId('received_by');

            $table->dropColumn([
                'sent_at',
                'received_at',
                'received_note',
            ]);
        });

        Schema::dropIfExists('classifications');
    }
};
