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

        $occurrences = self::countOccurrences($extracted->plainText, $context->focusKeyphrase);

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

    /**
     * Whole-phrase matches, case-insensitive. A match may not touch another letter or digit
     * on either side, so adjacent repeats ("coffee coffee") each count and punctuation after
     * the phrase ("coffee.") still counts, while "coffees" does not match "coffee". Space-
     * padded substr_count() missed both: the shared space made repeats overlap, and the
     * trailing space never matched before punctuation.
     */
    public static function countOccurrences(string $text, string $keyphrase): int
    {
        $words = preg_split('/\s+/u', mb_strtolower(trim($keyphrase)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($words === []) {
            return 0;
        }

        $phrase = implode('\s+', array_map(static fn (string $word): string => preg_quote($word, '/'), $words));

        return (int) preg_match_all('/(?<![\p{L}\p{N}])'.$phrase.'(?![\p{L}\p{N}])/u', mb_strtolower($text));
    }
}
