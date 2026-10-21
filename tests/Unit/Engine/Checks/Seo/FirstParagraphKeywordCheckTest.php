<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\FirstParagraphKeywordCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is Good when the keyphrase appears in the first paragraph', function (): void {
    $context = new ContentContext(
        contentHtml: '<p>This article is about coffee roasting basics.</p>',
        focusKeyphrase: 'coffee roasting',
    );

    expect((new FirstParagraphKeywordCheck)->run($context)->status)->toBe(Status::Good);
});

it('is Bad when the keyphrase is missing from the first paragraph', function (): void {
    $context = new ContentContext(
        contentHtml: '<p>This paragraph is about something else.</p>',
        focusKeyphrase: 'coffee roasting',
    );

    expect((new FirstParagraphKeywordCheck)->run($context)->status)->toBe(Status::Bad);
});
