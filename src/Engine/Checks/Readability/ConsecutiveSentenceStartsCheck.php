<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Readability;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class ConsecutiveSentenceStartsCheck implements Check
{
    public function __construct(
        private readonly int $maxRun = 3,
        private readonly int $weight = 4,
    ) {}

    public function id(): string
    {
        return 'consecutive-sentence-starts';
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
        return count($context->extracted()->sentences) >= $this->maxRun;
    }

    public function run(ContentContext $context): CheckResult
    {
        $sentences = $context->extracted()->sentences;

        $previousWord = null;
        $runLength = 0;
        $foundRun = false;

        foreach ($sentences as $sentence) {
            $firstWord = self::firstWord($sentence);

            if ($firstWord !== '' && $firstWord === $previousWord) {
                $runLength++;
            } else {
                $runLength = 1;
                $previousWord = $firstWord;
            }

            if ($runLength >= $this->maxRun) {
                $foundRun = true;
                break;
            }
        }

        $status = $foundRun ? Status::Bad : Status::Good;
        $message = $foundRun
            ? "Found {$this->maxRun} or more consecutive sentences starting with the same word."
            : 'No repetitive consecutive sentence starts were found.';

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }

    private static function firstWord(string $sentence): string
    {
        $words = preg_split('/\s+/u', trim($sentence), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return $words === [] ? '' : mb_strtolower($words[0]);
    }
}
