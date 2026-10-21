<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\MetaDescriptionKeywordCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is Good when the keyphrase appears in the description', function (): void {
    $context = new ContentContext(
        description: 'This guide compares the best coffee beans for espresso.',
        focusKeyphrase: 'best coffee beans for espresso',
    );

    expect((new MetaDescriptionKeywordCheck)->run($context)->status)->toBe(Status::Good);
});

it('is Bad when the keyphrase is missing', function (): void {
    $context = new ContentContext(description: 'Nothing relevant here at all.', focusKeyphrase: 'coffee roasting');

    expect((new MetaDescriptionKeywordCheck)->run($context)->status)->toBe(Status::Bad);
});
