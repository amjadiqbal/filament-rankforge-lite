<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Seo;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class SubheadingsKeywordCheck implements Check
{
    public function __construct(private readonly int $weight = 6) {}

    public function id(): string
    {
        return 'subheadings-keyword';
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
        return $context->focusKeyphrase !== '';
    }

    public function run(ContentContext $context): CheckResult
    {
        $headings = $context->extracted()->headings;

        if ($headings === []) {
            return new CheckResult(
                $this->id(),
                $this->group(),
                Status::Bad,
                'No subheadings were found in the content.',
                $this->weight,
            );
        }

        foreach ($headings as $heading) {
            if (mb_stripos($heading['text'], $context->focusKeyphrase) !== false) {
                return new CheckResult(
                    $this->id(),
                    $this->group(),
                    Status::Good,
                    'The focus keyphrase appears in at least one subheading.',
                    $this->weight,
                );
            }
        }

        return new CheckResult(
            $this->id(),
            $this->group(),
            Status::Bad,
            'The focus keyphrase does not appear in any subheading.',
            $this->weight,
        );
    }
}
