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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('key',100);
            $table->string('locale',10);
            $table->string('tag',50);
            $table->text('value');
            $table->timestamps();
            
            // Add composite unique index to prevent duplicates
            $table->unique(['key', 'locale', 'tag']);
            
            // Add indexes for performance
            $table->index('key');
            $table->index('locale');
            $table->index('tag');
            // Composite index for common queries
            $table->index(['locale', 'tag']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
