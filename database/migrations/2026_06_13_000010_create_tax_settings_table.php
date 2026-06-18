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
        Schema::create('tax_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->unsignedSmallInteger('year');
            $table->decimal('pph21_rate', 5, 2)->nullable()->comment('Persentase PPh 21');
            $table->decimal('bpjs_kesehatan_rate', 5, 2)->default(4.00)->comment('Kontribusi karyawan');
            $table->decimal('bpjs_ketenagakerjaan_rate', 5, 2)->default(0.24);
            $table->decimal('jht_rate', 5, 2)->default(3.97);
            $table->decimal('non_taxable_income', 12, 2)->nullable()->comment('Penghasilan tidak kena pajak (PTKP)');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['company_id', 'year']);
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_settings');
    }
};
