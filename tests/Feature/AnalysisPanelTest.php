<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Report;
use AmjadIqbal\FilamentRankForge\Filament\Livewire\AnalysisPanel;

/**
 * Livewire::test() hits an unrelated ViewErrorBag/session plumbing error in
 * this Testbench environment (same issue worked around in SeoFieldsTest) —
 * so this exercises the component directly instead. That still covers the
 * part that matters: mount() mapping, refreshAnalysis()'s field merging,
 * report() computation, and setSerpDevice()'s validation. The #[On(...)]
 * event-dispatch wiring itself is Livewire's own well-tested plumbing, not
 * something this package needs to re-verify.
 */
it('mounts with the given SEO data and computes a report', function (): void {
    $panel = new AnalysisPanel;
    $panel->mount(title: 'A Fairly Short Title For This One', focusKeyphrase: 'coffee');

    expect($panel->report())->toBeInstanceOf(Report::class)
        ->and($panel->report()->results)->not->toBeEmpty();
});

it('produces an empty report for empty data', function (): void {
    $panel = new AnalysisPanel;
    $panel->mount();

    $report = $panel->report();

    expect($report->results)->toBe([])
        ->and($report->score())->toBeNull();
});

it('merges incoming data onto existing state via refreshAnalysis, keeping unspecified fields', function (): void {
    $panel = new AnalysisPanel;
    $panel->mount(title: 'Too Short', slug: 'existing-slug');

    $panel->refreshAnalysis([
        'title' => 'Best Coffee Beans for Espresso — The Complete Buying Guide',
        'focusKeyphrase' => 'coffee',
    ]);

    expect($panel->title)->toBe('Best Coffee Beans for Espresso — The Complete Buying Guide')
        ->and($panel->focusKeyphrase)->toBe('coffee')
        ->and($panel->slug)->toBe('existing-slug');
});

it('builds a ContentContext matching its own public state', function (): void {
    $panel = new AnalysisPanel;
    $panel->mount(
        title: 'Title',
        description: 'Description',
        slug: 'slug',
        canonicalUrl: 'https://example.com/slug',
        contentHtml: '<p>Body</p>',
        focusKeyphrase: 'keyword',
        relatedKeyphrases: ['related'],
        language: 'es',
        cornerstone: true,
    );

    $context = $panel->toContentContext();

    expect($context->title)->toBe('Title')
        ->and($context->description)->toBe('Description')
        ->and($context->slug)->toBe('slug')
        ->and($context->canonicalUrl)->toBe('https://example.com/slug')
        ->and($context->contentHtml)->toBe('<p>Body</p>')
        ->and($context->focusKeyphrase)->toBe('keyword')
        ->and($context->relatedKeyphrases)->toBe(['related'])
        ->and($context->language)->toBe('es')
        ->and($context->cornerstone)->toBeTrue();
});

it('toggles the SERP preview device between desktop and mobile, ignoring invalid values', function (): void {
    $panel = new AnalysisPanel;
    $panel->mount();

    expect($panel->serpDevice)->toBe('desktop');

    $panel->setSerpDevice('mobile');
    expect($panel->serpDevice)->toBe('mobile');

    $panel->setSerpDevice('not-a-real-device');
    expect($panel->serpDevice)->toBe('mobile');
});

it('renders the Blade view without error for a populated report', function (): void {
    $panel = new AnalysisPanel;
    $panel->mount(
        title: 'Best Coffee Beans for Espresso — The Complete Buying Guide',
        description: str_repeat('a', 140),
        slug: 'best-coffee-beans-for-espresso',
        contentHtml: '<h2>Heading</h2><p>'.str_repeat('word ', 300).'</p>',
        focusKeyphrase: 'best coffee beans for espresso',
    );

    $html = $panel->render()->render();

    expect($html)->toContain('SEO')
        ->and($html)->not->toBeEmpty();
});

it('renders the Blade view without error for an empty report', function (): void {
    $panel = new AnalysisPanel;
    $panel->mount();

    expect($panel->render()->render())->not->toBeEmpty();
});
