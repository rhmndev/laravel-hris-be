<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('permissions')) {
            return;
        }

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $employeeRole = Role::firstOrCreate(['name' => 'employee']);

        if (Schema::hasColumn('users', 'role')) {
            User::query()
                ->select(['id', 'role'])
                ->chunkById(200, function ($users) use ($adminRole, $employeeRole) {
                    foreach ($users as $user) {
                        $roleName = $user->role === 'admin' ? 'admin' : 'employee';
                        $user->assignRole($roleName);
                    }
                });

            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['admin', 'employee'])->default('employee')->after('employee_id');
            });
        }
    }
};
