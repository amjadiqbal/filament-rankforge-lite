<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\CheckRegistry;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\SeoAnalyzer;
use AmjadIqbal\FilamentRankForge\Engine\Status;
use AmjadIqbal\FilamentRankForge\Engine\WeightedCheck;

/**
 * Regression: rankforge.analysis.{disabled_checks, weights_override, keyword_density_range,
 * content_length_tiers} were documented in the config and BLUEPRINT but nothing read them.
 * The engine is framework-free, so these pass the settings explicitly.
 */
function analyzeWith(array $config, array $context): array
{
    $analyzer = new SeoAnalyzer(new CheckRegistry(CheckRegistry::defaults($config)));
    $results = [];

    foreach ($analyzer->run(ContentContext::fromArray($context))->results as $result) {
        $results[$result->id] = $result;
    }

    return $results;
}

function byId(array $checks, string $id): ?object
{
    foreach ($checks as $check) {
        if ($check->id() === $id) {
            return $check;
        }
    }

    return null;
}

/**
 * 100 words, 4 of them the keyphrase: a 4% density. The occurrences are not adjacent because
 * KeywordDensityCheck counts ' espresso ' with substr_count(), which would see only every
 * other one of four back-to-back repeats.
 */
function densityContent(): string
{
    return '<p>'.str_repeat('filler ', 92).str_repeat('espresso filler ', 4).'</p>';
}

it('leaves the defaults unchanged when the config is empty', function (): void {
    expect(CheckRegistry::defaults([]))->toHaveCount(22)
        ->and(CheckRegistry::defaults(['disabled_checks' => [], 'weights_override' => []]))->toHaveCount(22);
});

it('drops every check listed in disabled_checks', function (): void {
    $checks = CheckRegistry::defaults(['disabled_checks' => ['passive-voice', 'outbound-links']]);

    expect($checks)->toHaveCount(20)
        ->and(byId($checks, 'passive-voice'))->toBeNull()
        ->and(byId($checks, 'outbound-links'))->toBeNull()
        ->and(byId($checks, 'title-length'))->not->toBeNull();
});

it('ignores unknown ids in disabled_checks', function (): void {
    expect(CheckRegistry::defaults(['disabled_checks' => ['no-such-check']]))->toHaveCount(22);
});

it('applies weights_override to the check and to its result', function (): void {
    $checks = CheckRegistry::defaults(['weights_override' => ['title-length' => 5]]);
    $check = byId($checks, 'title-length');

    expect($check)->toBeInstanceOf(WeightedCheck::class)
        ->and($check->weight())->toBe(5);

    $results = analyzeWith(['weights_override' => ['title-length' => 5]], ['title' => 'A reasonably sized title for testing weights']);

    expect($results['title-length'])->toBeInstanceOf(CheckResult::class)
        ->and($results['title-length']->weight)->toBe(5);
});

it('lets weights_override set a weight of zero and ignores invalid weights', function (): void {
    $checks = CheckRegistry::defaults(['weights_override' => ['title-length' => 0, 'slug-length' => -3, 'slug-keyword' => 'heavy']]);

    expect(byId($checks, 'title-length')->weight())->toBe(0)
        ->and(byId($checks, 'slug-length'))->not->toBeInstanceOf(WeightedCheck::class)
        ->and(byId($checks, 'slug-keyword'))->not->toBeInstanceOf(WeightedCheck::class);
});

it('changes the score when a weight is overridden', function (): void {
    $context = ContentContext::fromArray(['title' => 'Tiny', 'description' => str_repeat('d', 150)]);

    $default = (new SeoAnalyzer(new CheckRegistry(CheckRegistry::defaults([]))))->run($context)->score();
    $heavy = (new SeoAnalyzer(new CheckRegistry(CheckRegistry::defaults(['weights_override' => ['title-length' => 100]]))))->run($context)->score();

    expect($heavy)->not->toBe($default);
});

it('reads keyword_density_range', function (): void {
    $context = ['contentHtml' => densityContent(), 'focusKeyphrase' => 'espresso'];

    expect(analyzeWith([], $context)['keyword-density']->status)->toBe(Status::Bad)
        ->and(analyzeWith(['keyword_density_range' => [3.0, 5.0]], $context)['keyword-density']->status)->toBe(Status::Good);
});

it('falls back to the default density range for a malformed keyword_density_range', function (): void {
    $context = ['contentHtml' => densityContent(), 'focusKeyphrase' => 'espresso'];

    foreach ([['x', 'y'], [5.0, 1.0], [3.0], 'wide', [-1, 2]] as $bad) {
        expect(analyzeWith(['keyword_density_range' => $bad], $context)['keyword-density']->status)->toBe(Status::Bad);
    }
});

it('reads content_length_tiers', function (): void {
    $context = ['contentHtml' => '<p>'.str_repeat('word ', 150).'</p>'];

    expect(analyzeWith([], $context)['content-length']->status)->toBe(Status::Bad)
        ->and(analyzeWith(['content_length_tiers' => [100, 200, 300]], $context)['content-length']->status)->toBe(Status::Ok)
        ->and(analyzeWith(['content_length_tiers' => [100, 150, 300]], $context)['content-length']->status)->toBe(Status::Good);
});

it('falls back to the default tiers for malformed content_length_tiers', function (): void {
    $context = ['contentHtml' => '<p>'.str_repeat('word ', 150).'</p>'];

    foreach ([[], [400], ['a', 'b'], [600, 300], 'long'] as $bad) {
        expect(analyzeWith(['content_length_tiers' => $bad], $context)['content-length']->status)->toBe(Status::Bad);
    }
});

it('uses the built-in defaults when no Laravel config is available', function (): void {
    // No application container is bound in this framework-free suite, so config() throws.
    expect(CheckRegistry::defaults())->toHaveCount(22);
});
