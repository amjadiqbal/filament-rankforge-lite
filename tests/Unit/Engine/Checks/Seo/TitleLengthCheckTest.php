<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\TitleLengthCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is not applicable without a title', function (): void {
    expect((new TitleLengthCheck)->isApplicable(new ContentContext))->toBeFalse();
});

it('is Good in the 50-60 character range', function (): void {
    $context = new ContentContext(title: 'Best Coffee Beans for Espresso — The Complete Buying Guide');

    expect((new TitleLengthCheck)->run($context)->status)->toBe(Status::Good);
});

it('is Ok in the mid range', function (): void {
    $context = new ContentContext(title: 'A Fairly Short Title For This One');

    expect((new TitleLengthCheck)->run($context)->status)->toBe(Status::Ok);
});

it('is Bad when far too short', function (): void {
    $context = new ContentContext(title: 'Too Short');

    expect((new TitleLengthCheck)->run($context)->status)->toBe(Status::Bad);
});
