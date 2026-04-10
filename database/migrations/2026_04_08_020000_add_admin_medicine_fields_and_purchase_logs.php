<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            $table->date('expiry_date')->nullable()->after('sell_price');
            $table->string('photo_path')->nullable()->after('expiry_date');
        });

        Schema::create('medicine_purchase_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('medicine_id')->constrained('medicines')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('buy_price', 14, 2)->default(0);
            $table->date('expiry_date')->nullable();
            $table->string('photo_path')->nullable();
            $table->dateTime('purchased_at')->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_purchase_logs');

        Schema::table('medicines', function (Blueprint $table) {
            $table->dropColumn([
                'expiry_date',
                'photo_path',
            ]);
        });
    }
};

