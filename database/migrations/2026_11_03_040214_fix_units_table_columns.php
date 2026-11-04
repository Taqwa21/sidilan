<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (!Schema::hasColumn('units', 'name')) {
                $table->string('name')->after('id');
            }

            if (!Schema::hasColumn('units', 'code')) {
                $table->string('code')->unique()->after('name');
            }

            if (!Schema::hasColumn('units', 'description')) {
                $table->text('description')->nullable()->after('code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            if (Schema::hasColumn('units', 'code')) {
                $table->dropUnique(['code']);
            }

            $table->dropColumn(['name', 'code', 'description']);
        });
    }
};