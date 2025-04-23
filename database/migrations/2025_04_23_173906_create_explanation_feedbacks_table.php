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
        Schema::create('explanation_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bible_explanation_id')->constrained()->onDelete('cascade');
            $table->boolean('is_positive')->comment('true = positive feedback, false = negative feedback');
            $table->text('comment')->nullable()->comment('User suggestions for improvement');
            $table->string('testament', 10);
            $table->string('book', 50);
            $table->unsignedInteger('chapter');
            $table->string('verses', 100)->nullable();
            $table->string('user_ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
            
            // Index for fast queries
            $table->index(['testament', 'book', 'chapter', 'verses'], 'idx_feedback_search');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('explanation_feedbacks');
    }
};
