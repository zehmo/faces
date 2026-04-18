<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('reg_number', 50)->unique();
            $table->string('jamb_reg_number', 50)->nullable();
            $table->string('full_name', 255);
            $table->date('date_of_birth')->nullable();
            $table->enum('sex', ['Male', 'Female']);
            $table->enum('marital_status', ['Single', 'Married'])->default('Single');
            $table->foreignId('state_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lga_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('town_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone_number', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('level', ['100', '200', '300', '400'])->default('100');
            $table->string('photo_filename', 255)->default('');
            $table->timestamps();
            $table->softDeletes();

            $table->index('reg_number');
            $table->index('updated_at');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
