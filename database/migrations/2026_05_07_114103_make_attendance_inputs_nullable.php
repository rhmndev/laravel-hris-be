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
        //
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('check_in_latitude', 10, 8)->nullable()->change();
            $table->decimal('check_out_latitude', 10, 8)->nullable()->change();
            $table->decimal('check_in_longitude', 11, 8)->nullable()->change();
            $table->decimal('check_out_longitude', 11, 8)->nullable()->change();
            $table->string('check_in_photo')->nullable()->change();
            $table->string('check_out_photo')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('check_in_latitude', 10, 8)->nullable(false)->change();
            $table->decimal('check_out_latitude', 10, 8)->nullable(false)->change();
            $table->decimal('check_in_longitude', 11, 8)->nullable(false)->change();
            $table->decimal('check_out_longitude', 11, 8)->nullable(false)->change();
            $table->string('check_in_photo')->nullable(false)->change();
            $table->string('check_out_photo')->nullable(false)->change();
        });
    }
};
