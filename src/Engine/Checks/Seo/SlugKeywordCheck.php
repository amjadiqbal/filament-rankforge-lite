<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Seo;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class SlugKeywordCheck implements Check
{
    public function __construct(private readonly int $weight = 5) {}

    public function id(): string
    {
        return 'slug-keyword';
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
        return $context->slug !== '' && $context->focusKeyphrase !== '';
    }

    public function run(ContentContext $context): CheckResult
    {
        $keyphraseSlug = mb_strtolower(str_replace(' ', '-', trim($context->focusKeyphrase)));
        $found = mb_stripos($context->slug, $keyphraseSlug) !== false;

        $status = $found ? Status::Good : Status::Bad;
        $message = $found
            ? 'The focus keyphrase appears in the slug.'
            : 'The focus keyphrase does not appear in the slug.';

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }
}
