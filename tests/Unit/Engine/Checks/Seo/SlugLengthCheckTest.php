<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\SlugLengthCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is Good with 5 or fewer words', function (): void {
    $context = new ContentContext(slug: 'best-coffee-beans-for-espresso');

    expect((new SlugLengthCheck)->run($context)->status)->toBe(Status::Good);
});

it('is Ok with 6-8 words', function (): void {
    $context = new ContentContext(slug: 'this-slug-has-seven-words-here');

    expect((new SlugLengthCheck)->run($context)->status)->toBe(Status::Ok);
});

it('is Bad with more than 8 words', function (): void {
    $context = new ContentContext(slug: 'this-is-a-really-long-slug-with-way-too-many-words');

    expect((new SlugLengthCheck)->run($context)->status)->toBe(Status::Bad);
});
