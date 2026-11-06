<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letter_attachments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('letter_id')
                ->constrained('letters')
                ->cascadeOnDelete();

            $table->foreignId('letter_follow_up_id')
                ->nullable()
                ->constrained('letter_follow_ups')
                ->cascadeOnDelete();

            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('document_type', 50);

            $table->string('original_name');

            $table->string('file_path');

            $table->string('mime_type', 100);

            $table->unsignedBigInteger('file_size');

            $table->timestamps();

            $table->index(['letter_id', 'document_type']);
            $table->index(['letter_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letter_attachments');
    }
};
