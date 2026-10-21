<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property array<string, string>|null $meta_title_translations
 * @property array<string, string>|null $meta_description_translations
 */
class RankForgeMeta extends Model
{
    /**
     * Longest canonical_url / og_image the form accepts and the columns hold (the
     * 2026-10 widen_rankforge_meta_url_columns migration made the columns this long).
     */
    public const URL_MAX_LENGTH = 2048;

    protected $table = 'rankforge_meta';

    protected $guarded = [];

    protected $casts = [
        'related_keyphrases' => 'array',
        'robots' => 'array',
        'meta_title_translations' => 'array',
        'meta_description_translations' => 'array',
        'cornerstone' => 'boolean',
        'score' => 'integer',
        'seo_score' => 'integer',
        'readability_score' => 'integer',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * BLUEPRINT 5.20: the locale-aware meta title, falling back to the
     * plain (single-locale) meta_title column when the requested locale
     * has no stored translation — which is always the case for apps that
     * never touch translations, keeping this a no-op for them.
     */
    public function translatedMetaTitle(?string $locale = null): ?string
    {
        $translation = ($this->meta_title_translations ?? [])[$locale ?? app()->getLocale()] ?? null;

        return $translation !== null && $translation !== '' ? $translation : $this->meta_title;
    }

    public function translatedMetaDescription(?string $locale = null): ?string
    {
        $translation = ($this->meta_description_translations ?? [])[$locale ?? app()->getLocale()] ?? null;

        return $translation !== null && $translation !== '' ? $translation : $this->meta_description;
    }

    public function setTranslatedMetaTitle(string $locale, string $value): void
    {
        $this->meta_title_translations = [...($this->meta_title_translations ?? []), $locale => $value];
    }

    public function setTranslatedMetaDescription(string $locale, string $value): void
    {
        $this->meta_description_translations = [...($this->meta_description_translations ?? []), $locale => $value];
    }
}
