<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->decimal('sell_price', 14, 2)->default(0)->after('stock');
        });

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->boolean('is_dispensed')->default(false)->after('notes');
            $table->timestamp('dispensed_at')->nullable()->after('is_dispensed');
            $table->foreignId('dispensed_by')->nullable()->after('dispensed_at')->constrained('users')->nullOnDelete();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('invoice_number', 50)->unique();
            $table->foreignId('cashier_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('prescription_id')->nullable()->constrained('prescriptions')->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained('patients')->nullOnDelete();
            $table->string('patient_name')->nullable();
            $table->string('sale_type', 30)->default('non_prescription')->index();
            $table->unsignedInteger('total_items')->default(0);
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->dateTime('sold_at')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->string('medicine_name_snapshot');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2)->default(0);
            $table->string('note', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');

        Schema::table('prescriptions', function (Blueprint $table) {
            $table->dropForeign(['dispensed_by']);
            $table->dropColumn(['is_dispensed', 'dispensed_at', 'dispensed_by']);
        });

        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn('sell_price');
        });
    }
};
