<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Readability;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class SubheadingDistributionCheck implements Check
{
    public function __construct(
        private readonly int $goodMaxWords = 300,
        private readonly int $okMaxWords = 450,
        private readonly int $weight = 5,
    ) {}

    public function id(): string
    {
        return 'subheading-distribution';
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
        return $context->extracted()->wordCount > 0;
    }

    public function run(ContentContext $context): CheckResult
    {
        $gaps = $context->extracted()->headingWordGaps;
        $maxGap = $gaps === [] ? $context->extracted()->wordCount : max($gaps);

        $status = match (true) {
            $maxGap <= $this->goodMaxWords => Status::Good,
            $maxGap <= $this->okMaxWords => Status::Ok,
            default => Status::Bad,
        };

        $message = match ($status) {
            Status::Good => "The longest stretch without a subheading is {$maxGap} words — well distributed.",
            Status::Ok => "The longest stretch without a subheading is {$maxGap} words — consider adding one more.",
            Status::Bad => "The longest stretch without a subheading is {$maxGap} words — add more subheadings.",
        };

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }
}
