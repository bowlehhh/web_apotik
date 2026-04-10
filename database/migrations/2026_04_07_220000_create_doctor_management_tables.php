<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('medical_record_number', 50)->unique();
            $table->string('name');
            $table->string('gender', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('address', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('medicines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('trade_name')->nullable()->index();
            $table->string('dosage', 120)->nullable();
            $table->string('category', 120)->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->string('unit', 30)->default('strip');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('patient_visits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->dateTime('visit_date')->index();
            $table->text('complaint');
            $table->text('diagnosis')->nullable();
            $table->text('action_taken')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('selesai');
            $table->timestamps();

            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
            $table->foreign('doctor_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('patient_visit_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->dateTime('prescribed_at')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('patient_visit_id')->references('id')->on('patient_visits')->cascadeOnDelete();
            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
            $table->foreign('doctor_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('prescription_id');
            $table->unsignedBigInteger('medicine_id');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('dosage_instructions', 255);
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->foreign('prescription_id')->references('id')->on('prescriptions')->cascadeOnDelete();
            $table->foreign('medicine_id')->references('id')->on('medicines')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('patient_visits');
        Schema::dropIfExists('medicines');
        Schema::dropIfExists('patients');
    }
};

