<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('medicines', 'entry_source')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->string('entry_source', 20)->default('manual')->after('barcode')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('medicines', 'entry_source')) {
            Schema::table('medicines', function (Blueprint $table) {
                $table->dropColumn('entry_source');
            });
        }
    }
};

