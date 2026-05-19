<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bible_explanations', function (Blueprint $table) {
            $table->string('version', 10)->default('acf')->after('verses');
            $table->index(['version', 'testament', 'book', 'chapter', 'verses'], 'idx_bible_explanation_version_search');
            $table->unique(['version', 'testament', 'book', 'chapter', 'verses'], 'unique_bible_explanation_version');
        });

        Schema::table('bible_explanations', function (Blueprint $table) {
            $table->dropUnique('unique_bible_explanation');
            $table->dropIndex('idx_bible_explanation_search');
        });
    }

    public function down(): void
    {
        Schema::table('bible_explanations', function (Blueprint $table) {
            $table->unique(['testament', 'book', 'chapter', 'verses'], 'unique_bible_explanation');
            $table->index(['testament', 'book', 'chapter', 'verses'], 'idx_bible_explanation_search');
            $table->dropUnique('unique_bible_explanation_version');
            $table->dropIndex('idx_bible_explanation_version_search');
            $table->dropColumn('version');
        });
    }
};
