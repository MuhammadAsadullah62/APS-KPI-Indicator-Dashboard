<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('observation_sessions', function (Blueprint $table) {
            $table->text('session_notes')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('observation_sessions', function (Blueprint $table) {
            $table->dropColumn('session_notes');
        });
    }
};
