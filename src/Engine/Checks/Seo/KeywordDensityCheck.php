<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Seo;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class KeywordDensityCheck implements Check
{
    public function __construct(
        private readonly float $goodMin = 0.5,
        private readonly float $goodMax = 2.5,
        private readonly float $okMin = 0.1,
        private readonly float $okMax = 3.5,
        private readonly int $weight = 8,
    ) {}

    /**
     * Whole-word occurrences of the keyphrase, case-insensitive, with any whitespace between its
     * words. Boundaries are lookarounds, so they do not consume the separator: back-to-back
     * repeats ("espresso espresso") each count, and punctuation next to the phrase ("espresso,")
     * does not hide it. The old ' kw ' substr_count() missed both, which skewed the score.
     */
    private function countOccurrences(string $plainText, string $keyphrase): int
    {
        $words = preg_split('/\s+/u', mb_strtolower(trim($keyphrase)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($words === []) {
            return 0;
        }

        $pattern = '/(?<![\p{L}\p{N}])'.implode('\\s+', array_map(static fn (string $word): string => preg_quote($word, '/'), $words)).'(?![\p{L}\p{N}])/u';

        return (int) preg_match_all($pattern, mb_strtolower($plainText));
    }

    public function id(): string
    {
        return 'keyword-density';
    }

    public function group(): string
    {
        return 'seo';
    }

    public function weight(): int
    {
        return $this->weight;
    }

    public function isApplicable(ContentContext $context): bool
    {
        return $context->focusKeyphrase !== '' && $context->extracted()->wordCount > 0;
    }

    public function run(ContentContext $context): CheckResult
    {
        $extracted = $context->extracted();

        $occurrences = $this->countOccurrences($extracted->plainText, $context->focusKeyphrase);

        $density = ($occurrences / $extracted->wordCount) * 100;
        $formatted = round($density, 2);

        $status = match (true) {
            $density >= $this->goodMin && $density <= $this->goodMax => Status::Good,
            $density >= $this->okMin && $density <= $this->okMax => Status::Ok,
            default => Status::Bad,
        };

        $message = match ($status) {
            Status::Good => "Keyword density ({$formatted}%) is in the ideal range.",
            Status::Ok => "Keyword density ({$formatted}%) is outside the ideal {$this->goodMin}%-{$this->goodMax}% range.",
            Status::Bad => $occurrences === 0
                ? 'The focus keyphrase does not appear in the content.'
                : "Keyword density ({$formatted}%) suggests keyword stuffing or the keyphrase is missing.",
        };

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }
}
