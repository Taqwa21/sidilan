
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disposition_targets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('disposition_target_letter_disposition', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('letter_disposition_id');
            $table->unsignedBigInteger('disposition_target_id');

            $table->foreign('letter_disposition_id', 'fk_dtl_disposition')
                ->references('id')
                ->on('letter_dispositions')
                ->cascadeOnDelete();

            $table->foreign('disposition_target_id', 'fk_dtl_target')
                ->references('id')
                ->on('disposition_targets')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['letter_disposition_id', 'disposition_target_id'],
                'uq_dtl_disposition_target'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disposition_target_letter_disposition');
        Schema::dropIfExists('disposition_targets');
    }
};
