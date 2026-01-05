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
            // Composite index for main query pattern
            $table->index(['testament', 'book', 'chapter', 'verses'], 'idx_testament_book_chapter_verses');

            // Index for popular explanations query
            $table->index('access_count', 'idx_access_count');

            // Index for feedback-based queries
            $table->index(['negative_feedback_count', 'positive_feedback_count'], 'idx_feedback_counts');

            // Index for source filtering
            $table->index('source', 'idx_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bible_explanations', function (Blueprint $table) {
            $table->dropIndex('idx_testament_book_chapter_verses');
            $table->dropIndex('idx_access_count');
            $table->dropIndex('idx_feedback_counts');
            $table->dropIndex('idx_source');
        });
    }
};
