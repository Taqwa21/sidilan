
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letters', function (Blueprint $table) {
            $table->id();

            // Informasi utama surat
            $table->string('letter_number')->unique();
            $table->date('letter_date');
            $table->string('origin');
            $table->string('subject');
            $table->text('description')->nullable();

            // Status dan pengguna pembuat
            $table->string('status')->default('draft')->index();

            $table->foreignId('created_by')
                ->constrained('users')
                ->restrictOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};
