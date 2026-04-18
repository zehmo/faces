<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_session_id')->constrained()->cascadeOnDelete();
            $table->boolean('school_fees_paid')->default(false);
            $table->date('school_fees_date_paid')->nullable();
            $table->boolean('departmental_dues_paid')->default(false);
            $table->boolean('faculty_dues_paid')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'academic_session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_fees');
    }
};
