<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Seo;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class CornerstoneDepthCheck implements Check
{
    public function __construct(
        private readonly int $goodWordCount = 900,
        private readonly int $goodHeadingCount = 3,
        private readonly int $weight = 6,
    ) {}

    public function id(): string
    {
        return 'cornerstone-depth';
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
        return $context->cornerstone;
    }

    public function run(ContentContext $context): CheckResult
    {
        $extracted = $context->extracted();
        $wordCount = $extracted->wordCount;
        $headingCount = count($extracted->headings);

        $status = match (true) {
            $wordCount >= $this->goodWordCount && $headingCount >= $this->goodHeadingCount => Status::Good,
            $wordCount >= (int) ($this->goodWordCount * 2 / 3) && $headingCount >= (int) ceil($this->goodHeadingCount / 2) => Status::Ok,
            default => Status::Bad,
        };

        $message = match ($status) {
            Status::Good => "Cornerstone content meets the depth target ({$wordCount} words, {$headingCount} subheadings).",
            Status::Ok => "Cornerstone content is close but not yet deep enough ({$wordCount} words, {$headingCount} subheadings) — aim for {$this->goodWordCount}+ words and {$this->goodHeadingCount}+ subheadings.",
            Status::Bad => "Cornerstone content is too shallow ({$wordCount} words, {$headingCount} subheadings) — aim for {$this->goodWordCount}+ words and {$this->goodHeadingCount}+ subheadings.",
        };

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }
}
