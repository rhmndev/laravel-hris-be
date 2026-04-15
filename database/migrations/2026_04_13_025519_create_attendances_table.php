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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('attendance_date');
            $table->datetime('check_in_time');
            $table->decimal('check_in_latitude', 10, 8);
            $table->decimal('check_in_longitude', 11, 8);
            $table->string('check_in_photo');
            $table->datetime('check_out_time')->nullable();
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            $table->string('check_out_photo')->nullable();
            $table->enum('status', ['on_time', 'late'])->default('on_time');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'attendance_date']);
            $table->unique(['user_id', 'attendance_date']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
