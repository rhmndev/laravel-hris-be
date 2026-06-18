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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->time('clock_in_time');
            $table->time('clock_out_time');
            $table->integer('late_tolerance_minutes')->default(15);
            $table->time('early_clock_in_time')->nullable()->comment('Jam masuk paling awal');
            $table->time('allow_clock_out_till')->nullable()->comment('Jam pulang paling lama');
            $table->boolean('self_clocking')->default(true)->comment('Karyawan bisa set jam sendiri');
            $table->timestamps();

            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
