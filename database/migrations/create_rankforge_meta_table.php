<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rankforge_meta', function (Blueprint $table): void {
            $table->id();
            $table->morphs('model');

            $table->string('focus_keyphrase')->nullable();
            $table->json('related_keyphrases')->nullable();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->json('robots')->nullable();

            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();

            $table->boolean('cornerstone')->default(false);
            $table->string('language')->nullable();
            $table->string('schema_type')->nullable();

            $table->unsignedTinyInteger('score')->nullable();
            $table->unsignedTinyInteger('seo_score')->nullable();
            $table->unsignedTinyInteger('readability_score')->nullable();

            $table->timestamps();

            $table->unique(['model_type', 'model_id']);
            $table->index('score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rankforge_meta');
    }
};
