<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thematic_studies', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('term');
            $table->longText('content');
            $table->string('source')->default('ai');
            $table->unsignedInteger('access_count')->default(0);
            $table->timestamps();

            $table->index(['slug', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thematic_studies');
    }
};
