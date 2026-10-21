<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\TitleKeywordCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is not applicable without a focus keyphrase', function (): void {
    $context = new ContentContext(title: 'Some Title');

    expect((new TitleKeywordCheck)->isApplicable($context))->toBeFalse();
});

it('is Good when the keyphrase appears near the start', function (): void {
    $context = new ContentContext(
        title: 'Best Coffee Beans for Espresso — The Complete Buying Guide',
        focusKeyphrase: 'best coffee beans for espresso',
    );

    expect((new TitleKeywordCheck)->run($context)->status)->toBe(Status::Good);
});

it('is Ok when the keyphrase appears late in the title', function (): void {
    $context = new ContentContext(
        title: 'A Complete Guide To Something Great About coffee roasting',
        focusKeyphrase: 'coffee roasting',
    );

    expect((new TitleKeywordCheck)->run($context)->status)->toBe(Status::Ok);
});

it('is Bad when the keyphrase is missing', function (): void {
    $context = new ContentContext(title: 'A totally unrelated title', focusKeyphrase: 'coffee roasting');

    expect((new TitleKeywordCheck)->run($context)->status)->toBe(Status::Bad);
});
