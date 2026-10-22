<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Report;
use AmjadIqbal\FilamentRankForge\Filament\Forms\AnalysisPanel;

function renderPanel(array $data): string
{
    return view(AnalysisPanel::VIEW, AnalysisPanel::viewData($data))->render();
}

it('computes a report from the form data', function (): void {
    $data = AnalysisPanel::viewData(['title' => 'A Fairly Short Title For This One', 'focusKeyphrase' => 'coffee']);

    expect($data['report'])->toBeInstanceOf(Report::class)
        ->and($data['report']->results)->not->toBeEmpty();
});

it('produces an empty report for empty data', function (): void {
    $report = AnalysisPanel::viewData([])['report'];

    expect($report->results)->toBe([])
        ->and($report->score())->toBeNull();
});

it('ignores keys that are not ContentContext fields', function (): void {
    $data = AnalysisPanel::viewData(['title' => 'Title', 'ogImage' => 'https://example.test/a.png', 'unknown' => 'x']);

    expect($data['title'])->toBe('Title')
        ->and($data['ogImage'])->toBe('https://example.test/a.png');
});

it('reads score thresholds and the upgrade-link switch from config', function (): void {
    config(['rankforge.score_thresholds.red_below' => 30, 'rankforge.lite.show_upgrade_link' => false]);

    $data = AnalysisPanel::viewData([]);

    expect($data['redThreshold'])->toBe(30)
        ->and($data['showUpgradeLink'])->toBeFalse()
        ->and(renderPanel([]))->not->toContain('rankforge.amjad.com.pk');
});

it('renders the gauge, check groups, SERP preview and social cards for a populated report', function (): void {
    $html = renderPanel([
        'title' => 'Best Coffee Beans for Espresso — The Complete Buying Guide',
        'description' => str_repeat('a', 140),
        'slug' => 'best-coffee-beans-for-espresso',
        'contentHtml' => '<h2>Heading</h2><p>'.str_repeat('word ', 300).'</p>',
        'focusKeyphrase' => 'best coffee beans for espresso',
        'ogImage' => 'https://example.test/og.png',
    ]);

    expect($html)->toContain('rfl-gauge')
        ->and($html)->toContain('Best Coffee Beans for Espresso — The Complete Buying Guide')
        ->and($html)->toContain('best-coffee-beans-for-espresso')
        ->and($html)->toContain('src="https://example.test/og.png"')
        ->and($html)->toContain("device = 'mobile'")
        ->and($html)->toContain('rankforge.amjad.com.pk');
});

it('renders placeholders for an empty report', function (): void {
    $html = renderPanel([]);

    expect($html)->toContain('Untitled')
        ->and($html)->toContain('No image set');
});

it('prefers the Open Graph overrides on the social cards', function (): void {
    $html = renderPanel(['title' => 'Meta title', 'ogTitle' => 'Social title', 'ogDescription' => 'Social description']);

    expect($html)->toContain('Social title')
        ->and($html)->toContain('Social description');
});

function relatedContext(array $related): array
{
    return [
        'title' => 'Best Coffee Beans for Espresso: A Buying Guide',
        'description' => 'Find the best coffee beans for espresso and learn how espresso beans differ.',
        'slug' => 'best-coffee-beans-for-espresso',
        'contentHtml' => '<p>Choosing the best coffee beans for espresso matters.</p><h2>Espresso beans</h2><p>'.str_repeat('word ', 200).'</p>',
        'focusKeyphrase' => 'best coffee beans for espresso',
        'relatedKeyphrases' => $related,
    ];
}

it('scores each related keyphrase on its own', function (): void {
    $data = AnalysisPanel::viewData(relatedContext(['espresso beans', 'french press']));

    expect(array_column($data['related'], 'keyphrase'))->toBe(['espresso beans', 'french press'])
        ->and($data['related'][0]['report'])->toBeInstanceOf(Report::class)
        ->and($data['related'][0]['report']->score())->toBeGreaterThan($data['related'][1]['report']->score());
});

it('does not let related keyphrases change the overall score', function (): void {
    $without = AnalysisPanel::viewData(relatedContext([]))['report']->score();
    $with = AnalysisPanel::viewData(relatedContext(['french press', 'cold brew']))['report']->score();

    expect($with)->toBe($without);
});

it('skips blank and duplicate related keyphrases, including the focus keyphrase', function (): void {
    $data = AnalysisPanel::viewData(relatedContext(['', 'Best Coffee Beans for Espresso', 'espresso beans', 'Espresso Beans']));

    expect(array_column($data['related'], 'keyphrase'))->toBe(['espresso beans']);
});

it('respects disabled checks for related keyphrases too', function (): void {
    config(['rankforge.analysis.disabled_checks' => ['keyword-density']]);

    $ids = array_map(fn ($r) => $r->id, AnalysisPanel::viewData(relatedContext(['espresso beans']))['related'][0]['report']->results);

    expect($ids)->not->toContain('keyword-density')->and($ids)->not->toBeEmpty();
});

it('renders the related keyphrase rows only when there are related keyphrases', function (): void {
    $with = renderPanel(relatedContext(['espresso beans']));
    $without = renderPanel(relatedContext([]));

    expect($with)->toContain('Related keyphrases')
        ->and($with)->toContain('espresso beans')
        ->and($with)->toContain('they don&#039;t change the overall score')
        ->and($without)->not->toContain('class="rfl-related-btn"');
});

it('words related keyphrase messages for that keyphrase, not the focus keyphrase', function (): void {
    $html = renderPanel(relatedContext(['french press']));
    $related = substr($html, strpos($html, 'class="rfl-related"'));
    $related = substr($related, 0, strpos($related, 'rfl-tabs'));

    expect($related)->toContain('This keyphrase does not appear in the title.')
        ->and($related)->not->toContain('The focus keyphrase');
});
