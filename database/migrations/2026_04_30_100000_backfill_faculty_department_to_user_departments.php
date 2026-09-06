<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One-time: faculty rows that still have legacy `department` but no `user_departments` rows.
     */
    public function up(): void
    {
        if (! Schema::hasTable('user_departments') || ! Schema::hasColumn('users', 'department')) {
            return;
        }

        $faculty = DB::table('users')
            ->where('role', UserRole::Faculty->value)
            ->whereNotNull('department')
            ->pluck('department', 'id');

        $now = now();
        foreach ($faculty as $userId => $dept) {
            if (! is_string($dept) || $dept === '') {
                continue;
            }
            $exists = DB::table('user_departments')->where('user_id', $userId)->exists();
            if ($exists) {
                continue;
            }
            DB::table('user_departments')->insert([
                'user_id' => $userId,
                'department' => $dept,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Non-destructive: do not remove pivot rows
    }
};
