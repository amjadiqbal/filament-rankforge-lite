<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Seo;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class SlugLengthCheck implements Check
{
    public function __construct(
        private readonly int $goodMax = 5,
        private readonly int $okMax = 8,
        private readonly int $weight = 3,
    ) {}

    public function id(): string
    {
        return 'slug-length';
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
        return $context->slug !== '';
    }

    public function run(ContentContext $context): CheckResult
    {
        $wordCount = count(array_filter(explode('-', trim($context->slug, '-'))));

        $status = match (true) {
            $wordCount <= $this->goodMax => Status::Good,
            $wordCount <= $this->okMax => Status::Ok,
            default => Status::Bad,
        };

        $message = match ($status) {
            Status::Good => "Slug is concise ({$wordCount} words).",
            Status::Ok => "Slug is a bit long ({$wordCount} words) — aim for {$this->goodMax} or fewer.",
            Status::Bad => "Slug is too long ({$wordCount} words) — aim for {$this->goodMax} or fewer.",
        };

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }
}
