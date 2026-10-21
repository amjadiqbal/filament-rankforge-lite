<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Concerns;

use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Report;
use AmjadIqbal\FilamentRankForge\Engine\SeoAnalyzer;
use AmjadIqbal\FilamentRankForge\Models\RankForgeMeta;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Add to any Eloquent model that should carry SEO data. The data lives in the
 * shared rankforge_meta table (one row per record), the same table the full
 * RankForge suite reads, so upgrading keeps every record's SEO fields.
 *
 * Deliberately has no bootHasSeo(): ralphjsmit/laravel-seo's HasSEO trait
 * declares bootHasSEO(), and PHP method names are case-insensitive, so a boot
 * method here would collide on any model using both traits. Scores are kept
 * in sync by RankForgeServiceProvider's model event listeners instead.
 */
trait HasSeo
{
    /**
     * @return MorphOne<RankForgeMeta, $this>
     */
    public function rankforgeMeta(): MorphOne
    {
        return $this->morphOne(RankForgeMeta::class, 'model');
    }

    public function toSeoContentContext(): ContentContext
    {
        $meta = $this->rankforgeMeta;

        return new ContentContext(
            title: $meta?->translatedMetaTitle() ?: $this->getSeoTitle(),
            description: $meta?->translatedMetaDescription() ?: $this->getSeoDescription(),
            slug: $this->getSeoSlug(),
            canonicalUrl: $meta?->canonical_url ?: $this->getSeoCanonicalUrl(),
            contentHtml: $this->getSeoContentHtml(),
            focusKeyphrase: $meta?->focus_keyphrase ?? '',
            relatedKeyphrases: $meta?->related_keyphrases ?? [],
            language: $meta?->language ?: config('rankforge.analysis.default_language', 'en'),
            cornerstone: (bool) ($meta?->cornerstone ?? false),
        );
    }

    public function seoReport(): Report
    {
        return SeoAnalyzer::analyzeModel($this);
    }

    /**
     * Re-scores the record and stores score/seo_score/readability_score on its
     * rankforge_meta row, creating the row if needed. Saves quietly, so it
     * never re-triggers the listeners that call it.
     */
    public function syncSeoScore(): void
    {
        if (! $this->exists) {
            return;
        }

        $report = $this->seoReport();

        $meta = $this->rankforgeMeta ?? $this->rankforgeMeta()->make();

        $meta->forceFill([
            'score' => $report->score(),
            'seo_score' => $report->seoScore(),
            'readability_score' => $report->readabilityScore(),
        ]);

        if (! $meta->exists || $meta->isDirty()) {
            $meta->saveQuietly();
        }

        $this->setRelation('rankforgeMeta', $meta);
    }

    /**
     * Override on the consuming model if the title lives on a different attribute.
     */
    protected function getSeoTitle(): string
    {
        return (string) ($this->title ?? $this->name ?? '');
    }

    /**
     * Override on the consuming model if the description lives on a different attribute.
     */
    protected function getSeoDescription(): string
    {
        return (string) ($this->description ?? $this->excerpt ?? '');
    }

    /**
     * Override on the consuming model if the slug lives on a different attribute.
     */
    protected function getSeoSlug(): string
    {
        return (string) ($this->slug ?? '');
    }

    /**
     * Override on the consuming model to return its public URL.
     */
    protected function getSeoCanonicalUrl(): string
    {
        return method_exists($this, 'getUrl') ? (string) $this->getUrl() : '';
    }

    /**
     * Override on the consuming model if the analyzable content lives on a different attribute.
     */
    protected function getSeoContentHtml(): string
    {
        return (string) ($this->content ?? $this->body ?? '');
    }
}
