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
            // Modificar a coluna chapter para permitir valores nulos
            $table->unsignedInteger('chapter')->nullable()->change();
            
            // Remover e recriar os índices e restrições para acomodar valores nulos
            $table->dropIndex('idx_bible_explanation_search');
            $table->dropUnique('unique_bible_explanation');
            
            // Recriar índices permitindo valores nulos
            $table->index(['testament', 'book', 'chapter', 'verses'], 'idx_bible_explanation_search');
            $table->unique(['testament', 'book', 'chapter', 'verses'], 'unique_bible_explanation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bible_explanations', function (Blueprint $table) {
            // Reverter a coluna chapter para não permitir valores nulos
            $table->unsignedInteger('chapter')->nullable(false)->change();
            
            // Remover e recriar os índices e restrições
            $table->dropIndex('idx_bible_explanation_search');
            $table->dropUnique('unique_bible_explanation');
            
            // Recriar índices sem permitir valores nulos
            $table->index(['testament', 'book', 'chapter', 'verses'], 'idx_bible_explanation_search');
            $table->unique(['testament', 'book', 'chapter', 'verses'], 'unique_bible_explanation');
        });
    }
};
