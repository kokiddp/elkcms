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
        Schema::create('cms_translations', function (Blueprint $table) {
            $table->id();
            $table->morphs('translatable'); // Creates translatable_type and translatable_id
            $table->string('locale', 10)->index();
            $table->string('field');
            $table->text('value')->nullable();
            $table->timestamps();

            // Composite indexes for performance
            $table->index(['translatable_type', 'translatable_id', 'locale']);
            $table->index(['translatable_type', 'translatable_id', 'field']);

            // Unique constraint: one translation per model/field/locale combination
            $table->unique(['translatable_type', 'translatable_id', 'locale', 'field'], 'translation_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_translations');
    }
};
