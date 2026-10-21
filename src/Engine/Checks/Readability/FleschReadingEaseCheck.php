<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Readability;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Language\LanguageRegistry;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class FleschReadingEaseCheck implements Check
{
    public function __construct(
        private readonly LanguageRegistry $languages = new LanguageRegistry,
        private readonly float $goodMin = 60.0,
        private readonly float $okMin = 30.0,
        private readonly int $weight = 8,
    ) {}

    public function id(): string
    {
        return 'flesch-reading-ease';
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
        return $this->languages->get($context->language)->supportsSyllableCounting()
            && $context->extracted()->sentences !== []
            && $context->extracted()->wordCount > 0;
    }

    public function run(ContentContext $context): CheckResult
    {
        $extracted = $context->extracted();
        $pack = $this->languages->get($context->language);
        $coefficients = $pack->fleschCoefficients();

        $words = $extracted->wordCount;
        $sentenceCount = count($extracted->sentences);

        $syllables = 0;
        foreach (preg_split('/\s+/u', $extracted->plainText, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $word) {
            $syllables += $pack->countSyllables($word);
        }

        $score = $coefficients['base']
            - $coefficients['wordsPerSentence'] * ($words / $sentenceCount)
            - $coefficients['syllablesPerWord'] * ($syllables / $words);

        $rounded = round($score, 1);

        $status = match (true) {
            $score >= $this->goodMin => Status::Good,
            $score >= $this->okMin => Status::Ok,
            default => Status::Bad,
        };

        $message = match ($status) {
            Status::Good => "Flesch reading ease score ({$rounded}) indicates easy-to-read content.",
            Status::Ok => "Flesch reading ease score ({$rounded}) indicates moderately difficult content.",
            Status::Bad => "Flesch reading ease score ({$rounded}) indicates the content is hard to read.",
        };

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }
}
