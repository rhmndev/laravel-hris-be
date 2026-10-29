<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->time('min_check_in_time')->nullable()->after('work_end_time');
            $table->time('max_check_in_time')->nullable()->after('min_check_in_time');
            $table->time('min_check_out_time')->nullable()->after('max_check_in_time');
            $table->time('max_check_out_time')->nullable()->after('min_check_out_time');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['min_check_in_time', 'max_check_in_time', 'min_check_out_time', 'max_check_out_time']);
        });
    }
};
