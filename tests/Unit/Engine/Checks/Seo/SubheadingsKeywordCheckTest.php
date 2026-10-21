<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\SubheadingsKeywordCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is Good when a subheading contains the keyphrase', function (): void {
    $context = new ContentContext(
        contentHtml: '<h2>Why coffee roasting matters</h2><p>text</p>',
        focusKeyphrase: 'coffee roasting',
    );

    expect((new SubheadingsKeywordCheck)->run($context)->status)->toBe(Status::Good);
});

it('is Bad when there are no subheadings at all', function (): void {
    $context = new ContentContext(contentHtml: '<p>No headings here.</p>', focusKeyphrase: 'coffee roasting');

    expect((new SubheadingsKeywordCheck)->run($context)->status)->toBe(Status::Bad);
});

it('is Bad when subheadings exist but none match', function (): void {
    $context = new ContentContext(
        contentHtml: '<h2>Unrelated Heading</h2><p>text</p>',
        focusKeyphrase: 'coffee roasting',
    );

    expect((new SubheadingsKeywordCheck)->run($context)->status)->toBe(Status::Bad);
});
