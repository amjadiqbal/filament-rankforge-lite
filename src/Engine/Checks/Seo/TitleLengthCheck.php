<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Seo;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class TitleLengthCheck implements Check
{
    public function __construct(
        private readonly int $goodMin = 50,
        private readonly int $goodMax = 60,
        private readonly int $okMin = 30,
        private readonly int $okMax = 65,
        private readonly int $weight = 8,
    ) {}

    public function id(): string
    {
        return 'title-length';
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
        return $context->title !== '';
    }

    public function run(ContentContext $context): CheckResult
    {
        $length = mb_strlen($context->title);

        $status = match (true) {
            $length >= $this->goodMin && $length <= $this->goodMax => Status::Good,
            $length >= $this->okMin && $length <= $this->okMax => Status::Ok,
            default => Status::Bad,
        };

        $message = match ($status) {
            Status::Good => "Title length ({$length} characters) is in the ideal range.",
            Status::Ok => "Title length ({$length} characters) is acceptable but not ideal — aim for {$this->goodMin}-{$this->goodMax} characters.",
            Status::Bad => "Title length ({$length} characters) is far from the ideal range of {$this->goodMin}-{$this->goodMax} characters.",
        };

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }
}
