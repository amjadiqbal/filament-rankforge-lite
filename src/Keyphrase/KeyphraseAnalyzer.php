<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Keyphrase;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckRegistry;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;

/**
 * Runs the keyword-specific SEO checks against each related keyphrase, in
 * addition to the primary focus keyphrase covered by the full Report.
 */
final class KeyphraseAnalyzer
{
    /**
     * @var string[]
     */
    private const KEYWORD_SPECIFIC_CHECK_IDS = [
        'title-keyword',
        'meta-description-keyword',
        'slug-keyword',
        'first-paragraph-keyword',
        'subheadings-keyword',
        'keyword-density',
    ];

    public function __construct(private readonly CheckRegistry $registry = new CheckRegistry) {}

    /**
     * @param  string[]  $related
     * @return string[]
     */
    public function dedupeRelated(string $primary, array $related): array
    {
        $seen = [mb_strtolower(trim($primary)) => true];
        $result = [];

        foreach ($related as $keyphrase) {
            $normalized = mb_strtolower(trim($keyphrase));

            if ($normalized === '' || isset($seen[$normalized])) {
                continue;
            }

            $seen[$normalized] = true;
            $result[] = $keyphrase;
        }

        return $result;
    }

    /**
     * @return array<string, CheckResult[]> keyed by related keyphrase
     */
    public function analyzeRelated(ContentContext $context): array
    {
        $related = $this->dedupeRelated($context->focusKeyphrase, $context->relatedKeyphrases);

        $checks = array_values(array_filter(
            $this->registry->all(),
            fn (Check $check): bool => in_array($check->id(), self::KEYWORD_SPECIFIC_CHECK_IDS, true),
        ));

        $output = [];

        foreach ($related as $keyphrase) {
            $variantContext = $context->withFocusKeyphrase($keyphrase);
            $results = [];

            foreach ($checks as $check) {
                if (! $check->isApplicable($variantContext)) {
                    continue;
                }
                $results[] = $check->run($variantContext);
            }

            $output[$keyphrase] = $results;
        }

        return $output;
    }
}
