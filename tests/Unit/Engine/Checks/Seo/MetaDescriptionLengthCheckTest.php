<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\MetaDescriptionLengthCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is not applicable without a description', function (): void {
    expect((new MetaDescriptionLengthCheck)->isApplicable(new ContentContext))->toBeFalse();
});

it('is Good in the 120-160 character range', function (): void {
    $context = new ContentContext(
        description: 'Not sure which beans to buy? This guide compares the best coffee beans for espresso by roast, origin, and freshness so every shot pulls consistently well.',
    );

    expect((new MetaDescriptionLengthCheck)->run($context)->status)->toBe(Status::Good);
});

it('is Ok in the mid range', function (): void {
    $context = new ContentContext(description: str_repeat('a', 100));

    expect((new MetaDescriptionLengthCheck)->run($context)->status)->toBe(Status::Ok);
});

it('is Bad when far too short', function (): void {
    $context = new ContentContext(description: 'Too short.');

    expect((new MetaDescriptionLengthCheck)->run($context)->status)->toBe(Status::Bad);
});
