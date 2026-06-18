<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modify the status enum to include 'submitted' and 'checked'
        DB::statement("ALTER TABLE `payrolls` MODIFY COLUMN `status` ENUM('draft','calculated','submitted','checked','approved','paid','rejected') DEFAULT 'draft'");
    }

    public function down(): void
    {
        // Revert any submitted/checked records to calculated before removing enum values
        DB::table('payrolls')->where('status', 'submitted')->update(['status' => 'calculated']);
        DB::table('payrolls')->where('status', 'checked')->update(['status' => 'calculated']);

        DB::statement("ALTER TABLE `payrolls` MODIFY COLUMN `status` ENUM('draft','calculated','approved','paid','rejected') DEFAULT 'draft'");
    }
};
