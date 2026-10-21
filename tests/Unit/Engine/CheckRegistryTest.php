<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\CheckRegistry;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\TitleLengthCheck;

it('ships all 22 default checks', function (): void {
    expect(new CheckRegistry)->all()->toHaveCount(22);
});

it('withoutCheck removes exactly one check without mutating the original', function (): void {
    $registry = new CheckRegistry;
    $withoutOne = $registry->withoutCheck('outbound-links');

    expect($withoutOne->all())->toHaveCount(21)
        ->and($registry->all())->toHaveCount(22);
});

it('withoutChecks removes multiple checks', function (): void {
    $registry = (new CheckRegistry)->withoutChecks(['outbound-links', 'passive-voice']);

    expect($registry->all())->toHaveCount(20);
});

it('withCheck appends a custom check', function (): void {
    $registry = (new CheckRegistry)->withCheck(new TitleLengthCheck(weight: 99));

    expect($registry->all())->toHaveCount(23);
});
