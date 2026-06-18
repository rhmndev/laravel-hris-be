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
        Schema::create('employee_designations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('designation_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('basic_salary', 12, 2)->comment('Gaji Pokok');
            $table->decimal('allowances', 12, 2)->default(0)->comment('Tunjangan (Transport, Makan, dll)');
            $table->boolean('is_active')->default(true);
            $table->date('effective_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'is_active']);
            $table->index('user_id');
            $table->index('designation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_designations');
    }
};
