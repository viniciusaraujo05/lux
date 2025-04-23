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
        Schema::create('bible_explanations', function (Blueprint $table) {
            $table->id();
            $table->string('testament', 10); // 'old' or 'new'
            $table->string('book', 50);
            $table->unsignedInteger('chapter');
            $table->string('verses', 100)->nullable(); // NULL = full chapter
            $table->text('explanation_text');
            $table->string('source', 50); // Source of explanation (ex: 'gpt-4', 'claude', 'manual')
            $table->unsignedInteger('access_count')->default(0);
            $table->timestamps();
            
            // Composite index for fast queries
            $table->index(['testament', 'book', 'chapter', 'verses'], 'idx_bible_explanation_search');
            
            // Unique constraint to prevent duplicates
            $table->unique(['testament', 'book', 'chapter', 'verses'], 'unique_bible_explanation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bible_explanations');
    }
};
