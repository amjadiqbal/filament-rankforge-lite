<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The SEO form accepts canonical and Open Graph image URLs up to 2048 characters
 * (RankForgeMeta::URL_MAX_LENGTH), but create_rankforge_meta_table made both columns
 * string(255), so a longer URL failed on save with "Data too long" on MySQL. Widen the
 * columns instead of editing the original migration, whose name existing installs
 * (including the Lite plugin's upgrade path) already recorded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rankforge_meta', function (Blueprint $table): void {
            $table->string('canonical_url', 2048)->nullable()->change();
            $table->string('og_image', 2048)->nullable()->change();
        });
    }

    /**
     * Narrowing back to 255 fails or truncates if a longer URL was stored since.
     */
    public function down(): void
    {
        Schema::table('rankforge_meta', function (Blueprint $table): void {
            $table->string('canonical_url', 255)->nullable()->change();
            $table->string('og_image', 255)->nullable()->change();
        });
    }
};
