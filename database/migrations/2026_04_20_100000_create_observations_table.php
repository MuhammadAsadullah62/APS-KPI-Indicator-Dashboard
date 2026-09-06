<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('observee_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('aggregate_percent')->nullable();
            $table->timestamps();

            $table->index(['observer_id', 'created_at']);
            $table->index(['observee_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observations');
    }
};
