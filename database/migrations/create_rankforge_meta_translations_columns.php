<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rankforge_meta', function (Blueprint $table): void {
            // BLUEPRINT 5.20: per-locale overrides, keyed by locale code
            // ({"en": "...", "es": "..."}) — interoperable with spatie/
            // laravel-translatable's own JSON convention, without RankForge
            // itself depending on that package. Nullable and additive: the
            // existing meta_title/meta_description string columns remain
            // the single-locale default and are always the fallback when a
            // locale has no translation stored here.
            $table->json('meta_title_translations')->nullable()->after('meta_title');
            $table->json('meta_description_translations')->nullable()->after('meta_description');
        });
    }

    public function down(): void
    {
        Schema::table('rankforge_meta', function (Blueprint $table): void {
            $table->dropColumn(['meta_title_translations', 'meta_description_translations']);
        });
    }
};
