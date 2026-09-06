<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observation_id')->constrained('observations')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['observation_id', 'sort_order']);
        });

        Schema::create('observation_session_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('observation_session_id')->constrained('observation_sessions')->cascadeOnDelete();
            $table->string('bucket', 32);
            $table->string('metric_name', 96);
            $table->decimal('rating', 5, 2);
            $table->timestamps();

            $table->unique(['observation_session_id', 'bucket', 'metric_name'], 'obs_session_scores_unique_metric');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observation_session_scores');
        Schema::dropIfExists('observation_sessions');
    }
};
