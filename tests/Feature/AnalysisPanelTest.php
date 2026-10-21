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
