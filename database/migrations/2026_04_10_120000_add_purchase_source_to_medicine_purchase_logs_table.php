<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicine_purchase_logs', function (Blueprint $table) {
            $table->string('purchase_source')->nullable()->after('buy_price');
        });
    }

    public function down(): void
    {
        Schema::table('medicine_purchase_logs', function (Blueprint $table) {
            $table->dropColumn('purchase_source');
        });
    }
};
