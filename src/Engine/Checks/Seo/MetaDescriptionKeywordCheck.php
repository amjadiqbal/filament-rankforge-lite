<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Seo;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class MetaDescriptionKeywordCheck implements Check
{
    public function __construct(private readonly int $weight = 7) {}

    public function id(): string
    {
        return 'meta-description-keyword';
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
        return $context->description !== '' && $context->focusKeyphrase !== '';
    }

    public function run(ContentContext $context): CheckResult
    {
        $found = mb_stripos($context->description, $context->focusKeyphrase) !== false;

        $status = $found ? Status::Good : Status::Bad;
        $message = $found
            ? 'The focus keyphrase appears in the meta description.'
            : 'The focus keyphrase does not appear in the meta description.';

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }
}
