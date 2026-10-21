<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Seo;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class ContentLengthCheck implements Check
{
    public function __construct(
        private readonly int $okThreshold = 300,
        private readonly int $goodThreshold = 600,
        private readonly int $weight = 8,
    ) {}

    public function id(): string
    {
        return 'content-length';
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
        return $context->contentHtml !== '';
    }

    public function run(ContentContext $context): CheckResult
    {
        $wordCount = $context->extracted()->wordCount;

        $status = match (true) {
            $wordCount >= $this->goodThreshold => Status::Good,
            $wordCount >= $this->okThreshold => Status::Ok,
            default => Status::Bad,
        };

        $message = match ($status) {
            Status::Good => "Content length ({$wordCount} words) is sufficient.",
            Status::Ok => "Content length ({$wordCount} words) is acceptable but could be longer — aim for {$this->goodThreshold}+ words.",
            Status::Bad => "Content length ({$wordCount} words) is too short — aim for at least {$this->okThreshold} words.",
        };

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }
}
