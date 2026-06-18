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
        Schema::create('deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name', 100)->comment('PPh 21, BPJS, Cicilan, dll');
            $table->enum('type', ['tax', 'social_security', 'loan', 'advance', 'other']);
            $table->text('description')->nullable();
            $table->boolean('is_fixed')->default(false)->comment('True: Jumlah fixed, False: Persentase');
            $table->decimal('amount', 12, 2)->nullable()->comment('Amount jika is_fixed = true');
            $table->decimal('percentage', 5, 2)->nullable()->comment('Percentage jika is_fixed = false');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['company_id', 'name']);
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};
