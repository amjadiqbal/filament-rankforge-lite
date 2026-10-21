<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Readability\SubheadingDistributionCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is Good for short content with no headings needed', function (): void {
    $context = new ContentContext(contentHtml: '<p>A short paragraph.</p>');

    expect((new SubheadingDistributionCheck)->run($context)->status)->toBe(Status::Good);
});

it('is Bad for long content with no headings at all', function (): void {
    $context = new ContentContext(contentHtml: '<p>'.str_repeat('word ', 460).'</p>');

    expect((new SubheadingDistributionCheck)->run($context)->status)->toBe(Status::Bad);
});
