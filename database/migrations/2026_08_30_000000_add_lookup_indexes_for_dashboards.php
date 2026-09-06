<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Every directory / ranking query filters on role (and often wing).
            $table->index(['role', 'wing'], 'users_role_wing_index');
        });

        Schema::table('media', function (Blueprint $table): void {
            // avatarMedia() = morphOne where collection_name = 'avatar' + latestOfMany().
            $table->index(
                ['mediable_type', 'mediable_id', 'collection_name'],
                'media_mediable_collection_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_role_wing_index');
        });

        Schema::table('media', function (Blueprint $table): void {
            $table->dropIndex('media_mediable_collection_index');
        });
    }
};
