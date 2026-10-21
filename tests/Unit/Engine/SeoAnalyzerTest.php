<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Report;
use AmjadIqbal\FilamentRankForge\Engine\SeoAnalyzer;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('analyzes a plain array via the static facade', function (): void {
    $report = SeoAnalyzer::analyze([
        'title' => 'A Fairly Short Title For This One',
        'focusKeyphrase' => 'coffee',
    ]);

    expect($report)->toBeInstanceOf(Report::class);
});

it('excludes a disabled check from the fluent chain', function (): void {
    $report = SeoAnalyzer::analyzer()
        ->withoutCheck('outbound-links')
        ->run(new ContentContext(contentHtml: '<p>'.str_repeat('word ', 700).'</p>'));

    $ids = array_map(fn ($result) => $result->id, $report->results);

    expect($ids)->not->toContain('outbound-links');
});

it('produces an empty report with a null score for an empty context', function (): void {
    $report = SeoAnalyzer::analyze(new ContentContext);

    expect($report->results)->toBe([])
        ->and($report->score())->toBeNull();
});

it('adds an extra check via withCheck()', function (): void {
    $customCheck = new class implements Check
    {
        public function id(): string
        {
            return 'always-bad';
        }

        public function group(): string
        {
            return 'seo';
        }

        public function weight(): int
        {
            return 1;
        }

        public function isApplicable(ContentContext $context): bool
        {
            return true;
        }

        public function run(ContentContext $context): CheckResult
        {
            return new CheckResult(
                id: $this->id(),
                group: $this->group(),
                status: Status::Bad,
                message: 'always bad',
                weight: $this->weight(),
            );
        }
    };

    $report = SeoAnalyzer::analyzer()
        ->withCheck($customCheck)
        ->run(new ContentContext);

    $ids = array_map(fn ($result) => $result->id, $report->results);

    expect($ids)->toContain('always-bad');
});

it('analyzes keyphrases via analyzeKeyphrases()', function (): void {
    $results = SeoAnalyzer::analyzer()->analyzeKeyphrases(new ContentContext(
        title: 'Coffee Brewing Guide',
        contentHtml: '<p>'.str_repeat('coffee espresso ', 50).'</p>',
        focusKeyphrase: 'coffee',
        relatedKeyphrases: ['espresso'],
    ));

    expect($results)->toBeArray()
        ->and($results)->toHaveKey('espresso');
});

it('analyzeModel() delegates to analyze() for a HasSeo-shaped object', function (): void {
    $model = new class
    {
        public function toSeoContentContext(): ContentContext
        {
            return new ContentContext(title: 'A Fairly Short Title For This One', focusKeyphrase: 'coffee');
        }
    };

    expect(SeoAnalyzer::analyzeModel($model))->toBeInstanceOf(Report::class);
});

it('throws when given an object with no toSeoContentContext() method', function (): void {
    SeoAnalyzer::analyze(new stdClass);
})->throws(InvalidArgumentException::class, 'must define a toSeoContentContext(): ContentContext method');
