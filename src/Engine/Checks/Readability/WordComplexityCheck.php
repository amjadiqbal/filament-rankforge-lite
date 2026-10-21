<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Readability;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Language\LanguageRegistry;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class WordComplexityCheck implements Check
{
    public function __construct(
        private readonly LanguageRegistry $languages = new LanguageRegistry,
        private readonly int $complexSyllables = 3,
        private readonly float $goodMaxPercent = 15.0,
        private readonly float $okMaxPercent = 20.0,
        private readonly int $weight = 5,
    ) {}

    public function id(): string
    {
        return 'word-complexity';
    }

    public function group(): string
    {
        return 'readability';
    }

    public function weight(): int
    {
        return $this->weight;
    }

    public function isApplicable(ContentContext $context): bool
    {
        return $context->extracted()->wordCount > 0
            && $this->languages->get($context->language)->supportsSyllableCounting();
    }

    public function run(ContentContext $context): CheckResult
    {
        $extracted = $context->extracted();
        $pack = $this->languages->get($context->language);

        $words = preg_split('/\s+/u', $extracted->plainText, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $complexCount = 0;
        foreach ($words as $word) {
            if ($pack->countSyllables($word) > $this->complexSyllables) {
                $complexCount++;
            }
        }

        $percent = round(($complexCount / count($words)) * 100, 1);

        $status = match (true) {
            $percent <= $this->goodMaxPercent => Status::Good,
            $percent <= $this->okMaxPercent => Status::Ok,
            default => Status::Bad,
        };

        $message = match ($status) {
            Status::Good => "{$percent}% of words are complex (>{$this->complexSyllables} syllables) — easy to read.",
            Status::Ok => "{$percent}% of words are complex (>{$this->complexSyllables} syllables) — consider simplifying.",
            Status::Bad => "{$percent}% of words are complex (>{$this->complexSyllables} syllables) — too many complex words.",
        };

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }
}
