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
        Schema::table('users', function (Blueprint $table) {
            // Add payroll-related fields
            $table->string('date_of_birth')->nullable()->after('phone');
            $table->string('identity_number', 20)->unique()->nullable()->after('date_of_birth')->comment('No. KTP');
            $table->string('bank_account_number', 50)->nullable()->after('identity_number');
            $table->string('bank_name', 100)->nullable()->after('bank_account_number');
            $table->string('address', 500)->nullable()->after('bank_name')->comment('Alamat Rumah');
            $table->string('phone_personal', 20)->nullable()->after('address');
            $table->date('join_date')->nullable()->after('phone_personal')->comment('Tanggal Bergabung');
            $table->enum('employee_status', ['aktif', 'kontrak', 'magang', 'resign'])->default('aktif')->after('join_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth',
                'identity_number',
                'bank_account_number',
                'bank_name',
                'address',
                'phone_personal',
                'join_date',
                'employee_status'
            ]);
        });
    }
};
