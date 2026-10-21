<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\SlugKeywordCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is Good when the keyphrase appears in the slug', function (): void {
    $context = new ContentContext(slug: 'best-coffee-beans-for-espresso', focusKeyphrase: 'best coffee beans for espresso');

    expect((new SlugKeywordCheck)->run($context)->status)->toBe(Status::Good);
});

it('is Bad when the keyphrase is missing from the slug', function (): void {
    $context = new ContentContext(slug: 'some-other-slug', focusKeyphrase: 'coffee roasting');

    expect((new SlugKeywordCheck)->run($context)->status)->toBe(Status::Bad);
});
