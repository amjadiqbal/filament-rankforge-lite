<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\Rating;
use AmjadIqbal\FilamentRankForge\Engine\Report;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('returns null score, and null rating for an empty report', function (): void {
    $report = new Report([]);

    expect($report->score())->toBeNull()
        ->and($report->rating())->toBeNull();
});

it('computes a weighted score from Good/Ok/Bad results', function (): void {
    $report = new Report([
        new CheckResult('a', 'seo', Status::Good, 'good', 10),
        new CheckResult('b', 'seo', Status::Ok, 'ok', 10),
        new CheckResult('c', 'seo', Status::Bad, 'bad', 10),
    ]);

    // (10*1.0 + 10*0.5 + 10*0.0) / 30 * 100 = 50
    expect($report->score())->toBe(50)
        ->and($report->rating())->toBe(Rating::NeedsImprovement);
});

it('splits sub-scores by group', function (): void {
    $report = new Report([
        new CheckResult('a', 'seo', Status::Good, 'good', 10),
        new CheckResult('b', 'readability', Status::Bad, 'bad', 10),
    ]);

    expect($report->seoScore())->toBe(100)
        ->and($report->readabilityScore())->toBe(0)
        ->and($report->score())->toBe(50);
});

it('filters results by status', function (): void {
    $report = new Report([
        new CheckResult('a', 'seo', Status::Good, 'good', 5),
        new CheckResult('b', 'seo', Status::Ok, 'ok', 5),
        new CheckResult('c', 'seo', Status::Bad, 'bad', 5),
    ]);

    expect($report->passed())->toHaveCount(1)
        ->and($report->warnings())->toHaveCount(1)
        ->and($report->failed())->toHaveCount(1);
});
