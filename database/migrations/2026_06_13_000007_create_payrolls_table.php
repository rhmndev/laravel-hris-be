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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('month')->comment('1-12');
            $table->unsignedSmallInteger('year');

            // Attendance Data
            $table->unsignedInteger('total_working_days')->nullable()->comment('Total hari kerja yang seharusnya');
            $table->unsignedInteger('present_days')->nullable()->comment('Hari masuk');
            $table->unsignedInteger('half_days')->default(0)->comment('Half day / cuti setengah hari');
            $table->unsignedInteger('late_days')->default(0)->comment('Hari terlambat');
            $table->unsignedInteger('absent_days')->default(0)->comment('Hari alpa');
            $table->unsignedInteger('paid_leave_days')->default(0)->comment('Cuti berbayar');
            $table->unsignedInteger('unpaid_leave_days')->default(0)->comment('Cuti tidak berbayar');
            $table->unsignedInteger('holiday_days')->default(0)->comment('Libur nasional');
            $table->unsignedInteger('sick_leave_days')->default(0)->comment('Cuti sakit');
            $table->unsignedInteger('personal_leave_days')->default(0)->comment('Cuti pribadi');

            // Salary Components
            $table->decimal('basic_salary', 12, 2);
            $table->decimal('total_earnings', 12, 2)->default(0)->comment('Subtotal semua penghasilan');
            $table->decimal('total_deductions', 12, 2)->default(0)->comment('Subtotal semua potongan');
            $table->decimal('gross_salary', 12, 2)->nullable()->comment('(basic_salary + total_earnings)');
            $table->decimal('net_salary', 12, 2)->nullable()->comment('Gaji bersih (gross - deductions)');

            // Tax & Compliance
            $table->decimal('pph21', 12, 2)->default(0)->comment('Pajak PPh 21');
            $table->decimal('bpjs_kesehatan', 12, 2)->default(0)->comment('BPJS Kesehatan');
            $table->decimal('bpjs_ketenagakerjaan', 12, 2)->default(0)->comment('BPJS Ketenagakerjaan');
            $table->decimal('jht', 12, 2)->default(0)->comment('Jaminan Hari Tua (JHT)');

            // Status & Tracking
            $table->enum('status', ['draft', 'calculated', 'approved', 'paid', 'rejected'])->default('draft');
            $table->date('payment_date')->nullable();
            $table->enum('payment_method', ['transfer', 'cash', 'check'])->nullable();
            $table->string('payment_reference', 100)->nullable()->comment('Reference number dari bank');
            $table->text('notes')->nullable();

            // Metadata
            $table->timestamp('calculated_at')->nullable();
            $table->foreignId('calculated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['company_id', 'user_id', 'month', 'year']);
            $table->index('status');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
