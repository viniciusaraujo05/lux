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
        Schema::table('bible_explanations', function (Blueprint $table) {
            $table->integer('positive_feedback_count')->default(0);
            $table->integer('negative_feedback_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bible_explanations', function (Blueprint $table) {
            $table->dropColumn(['positive_feedback_count', 'negative_feedback_count']);
        });
    }
};
