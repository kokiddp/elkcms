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
        Schema::create('test_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200)->nullable();
            $table->text('content')->nullable();
            $table->string('featured_image')->nullable();
            $table->datetime('published_at')->nullable();

            $table->string('slug')->unique();
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_posts');
    }
};
