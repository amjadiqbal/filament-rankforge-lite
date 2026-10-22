<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Filament\Forms;

use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Report;
use AmjadIqbal\FilamentRankForge\Engine\SeoAnalyzer;

/**
 * Builds the view data for the analysis panel (score gauge, check list, SERP
 * and social previews) that SeoFields renders inside the form.
 *
 * It's a plain schema view rather than a nested Livewire component: the form
 * re-renders on every live field update anyway, so the panel recomputes on
 * that same request instead of needing a second round trip per keystroke.
 */
final class AnalysisPanel
{
    public const VIEW = 'rankforge::filament.analysis-panel';

    /**
     * @param  array<string, mixed>  $data  ContentContext fields plus ogTitle/ogDescription/ogImage
     * @return array<string, mixed>
     */
    public static function viewData(array $data): array
    {
        $context = ContentContext::fromArray(array_intersect_key($data, array_flip([
            'title', 'description', 'slug', 'canonicalUrl', 'contentHtml',
            'focusKeyphrase', 'relatedKeyphrases', 'language', 'cornerstone',
        ])));

        // Related keyphrases run the keyword checks on their own and get their own mini score;
        // they don't change the overall score, which stays the one HasSeo stores.
        $related = [];
        foreach (SeoAnalyzer::analyzer()->analyzeKeyphrases($context) as $keyphrase => $results) {
            $related[] = ['keyphrase' => (string) $keyphrase, 'report' => new Report($results)];
        }

        return [
            'report' => SeoAnalyzer::analyze($context),
            'related' => $related,
            'title' => $context->title,
            'description' => $context->description,
            'slug' => $context->slug,
            'canonicalUrl' => $context->canonicalUrl,
            'ogTitle' => (string) ($data['ogTitle'] ?? ''),
            'ogDescription' => (string) ($data['ogDescription'] ?? ''),
            'ogImage' => (string) ($data['ogImage'] ?? ''),
            'redThreshold' => (int) config('rankforge.score_thresholds.red_below', 40),
            'greenThreshold' => (int) config('rankforge.score_thresholds.green_at_or_above', 70),
            'showUpgradeLink' => (bool) config('rankforge.lite.show_upgrade_link', true),
        ];
    }
}
