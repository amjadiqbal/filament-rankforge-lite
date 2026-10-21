<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Readability;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class SentenceLengthCheck implements Check
{
    public function __construct(
        private readonly int $maxWords = 20,
        private readonly float $goodMaxPercent = 20.0,
        private readonly float $okMaxPercent = 30.0,
        private readonly int $weight = 6,
    ) {}

    public function id(): string
    {
        return 'sentence-length';
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
        return $context->extracted()->sentences !== [];
    }

    public function run(ContentContext $context): CheckResult
    {
        $sentences = $context->extracted()->sentences;

        $longCount = 0;
        foreach ($sentences as $sentence) {
            $wordCount = count(preg_split('/\s+/u', trim($sentence), -1, PREG_SPLIT_NO_EMPTY) ?: []);
            if ($wordCount > $this->maxWords) {
                $longCount++;
            }
        }

        $percent = round(($longCount / count($sentences)) * 100, 1);

        $status = match (true) {
            $percent <= $this->goodMaxPercent => Status::Good,
            $percent <= $this->okMaxPercent => Status::Ok,
            default => Status::Bad,
        };

        $message = match ($status) {
            Status::Good => "{$percent}% of sentences exceed {$this->maxWords} words — well within the target.",
            Status::Ok => "{$percent}% of sentences exceed {$this->maxWords} words — consider shortening some.",
            Status::Bad => "{$percent}% of sentences exceed {$this->maxWords} words — too many long sentences.",
        };

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }
}
