<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Seo;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class TitleKeywordCheck implements Check
{
    public function __construct(private readonly int $weight = 9) {}

    public function id(): string
    {
        return 'title-keyword';
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
        return $context->title !== '' && $context->focusKeyphrase !== '';
    }

    public function run(ContentContext $context): CheckResult
    {
        $position = mb_stripos($context->title, $context->focusKeyphrase);

        $status = match (true) {
            $position === false => Status::Bad,
            $position <= (int) (mb_strlen($context->title) / 2) => Status::Good,
            default => Status::Ok,
        };

        $message = match ($status) {
            Status::Good => 'The focus keyphrase appears near the beginning of the title.',
            Status::Ok => 'The focus keyphrase appears in the title, but not near the beginning.',
            Status::Bad => 'The focus keyphrase does not appear in the title.',
        };

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }
}
