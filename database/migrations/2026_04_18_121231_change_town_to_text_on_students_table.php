<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['town_id']);
            $table->dropColumn('town_id');
            $table->string('town', 100)->nullable()->after('lga_id');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('town');
            $table->foreignId('town_id')->nullable()->after('lga_id')->constrained()->nullOnDelete();
        });
    }
};
