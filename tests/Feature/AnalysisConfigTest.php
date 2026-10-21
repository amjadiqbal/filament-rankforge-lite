<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\SeoAnalyzer;
use AmjadIqbal\FilamentRankForge\Engine\Status;

/**
 * The same four settings, read through the real config repository, as an application
 * (and the live analysis panel) uses them.
 */
function resultIds(array $context): array
{
    return array_map(static fn ($result): string => $result->id, SeoAnalyzer::analyze($context)->results);
}

it('applies rankforge.analysis.disabled_checks from config', function (): void {
    $context = ['title' => 'A reasonably sized title', 'contentHtml' => '<p>It was decided by the team that the page is read by users.</p>'];

    expect(resultIds($context))->toContain('passive-voice');

    config(['rankforge.analysis.disabled_checks' => ['passive-voice']]);

    expect(resultIds($context))->not->toContain('passive-voice')
        ->and(resultIds($context))->toContain('title-length');
});

it('applies rankforge.analysis.weights_override from config', function (): void {
    config(['rankforge.analysis.weights_override' => ['title-length' => 7]]);

    $weights = [];
    foreach (SeoAnalyzer::analyze(['title' => 'A reasonably sized title for the weight test'])->results as $result) {
        $weights[$result->id] = $result->weight;
    }

    expect($weights['title-length'])->toBe(7);
});

it('applies rankforge.analysis.keyword_density_range and content_length_tiers from config', function (): void {
    $context = ['contentHtml' => '<p>'.str_repeat('filler ', 92).str_repeat('espresso filler ', 4).'</p>', 'focusKeyphrase' => 'espresso'];
    $status = static function (string $id) use ($context): Status {
        foreach (SeoAnalyzer::analyze($context)->results as $result) {
            if ($result->id === $id) {
                return $result->status;
            }
        }

        throw new RuntimeException("no result for {$id}");
    };

    expect($status('keyword-density'))->toBe(Status::Bad)
        ->and($status('content-length'))->toBe(Status::Bad);

    config(['rankforge.analysis.keyword_density_range' => [3.0, 5.0], 'rankforge.analysis.content_length_tiers' => [50, 90, 200]]);

    expect($status('keyword-density'))->toBe(Status::Good)
        ->and($status('content-length'))->toBe(Status::Good);
});
