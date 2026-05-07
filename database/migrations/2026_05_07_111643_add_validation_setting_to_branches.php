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
        Schema::table('branches', function (Blueprint $table) {
            $table->boolean('require_liveness')->default(true)->after('work_end_time');
            $table->boolean('require_geolocation')->default(true)->after('require_liveness');
            $table->boolean('require_face_recognation')->default(true)->after('require_geolocation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['require_liveness', 'require_geolocation', 'require_face_recognation']);
        });
    }
};
