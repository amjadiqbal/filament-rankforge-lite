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

        $haystack = ' '.mb_strtolower(preg_replace('/\s+/u', ' ', $extracted->plainText) ?? '').' ';
        $needle = ' '.mb_strtolower(trim($context->focusKeyphrase)).' ';
        $occurrences = substr_count($haystack, $needle);

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
