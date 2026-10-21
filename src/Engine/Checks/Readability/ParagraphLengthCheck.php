<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Readability;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class ParagraphLengthCheck implements Check
{
    public function __construct(
        private readonly int $maxWords = 150,
        private readonly float $okMaxPercent = 20.0,
        private readonly int $weight = 5,
    ) {}

    public function id(): string
    {
        return 'paragraph-length';
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
        return $context->extracted()->paragraphs !== [];
    }

    public function run(ContentContext $context): CheckResult
    {
        $paragraphs = $context->extracted()->paragraphs;

        $overLongCount = 0;
        foreach ($paragraphs as $paragraph) {
            $wordCount = count(preg_split('/\s+/u', trim($paragraph), -1, PREG_SPLIT_NO_EMPTY) ?: []);
            if ($wordCount > $this->maxWords) {
                $overLongCount++;
            }
        }

        $percent = round(($overLongCount / count($paragraphs)) * 100, 1);

        $status = match (true) {
            $overLongCount === 0 => Status::Good,
            $percent <= $this->okMaxPercent => Status::Ok,
            default => Status::Bad,
        };

        $message = match ($status) {
            Status::Good => "No paragraphs exceed {$this->maxWords} words.",
            Status::Ok => "{$overLongCount} paragraph(s) exceed {$this->maxWords} words — consider breaking them up.",
            Status::Bad => "{$overLongCount} paragraph(s) exceed {$this->maxWords} words — too many long paragraphs.",
        };

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }
}
