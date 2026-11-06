
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('letter_dispositions', function (Blueprint $table) {
            $table->text('target_units')->nullable()->after('instruction');
        });
    }

    public function down(): void
    {
        Schema::table('letter_dispositions', function (Blueprint $table) {
            $table->dropColumn('target_units');
        });
    }
};
