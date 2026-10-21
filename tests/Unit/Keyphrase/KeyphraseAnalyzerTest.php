<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Keyphrase\KeyphraseAnalyzer;

it('dedupes related keyphrases against the primary, case-insensitively', function (): void {
    $analyzer = new KeyphraseAnalyzer;

    $deduped = $analyzer->dedupeRelated('coffee', ['Coffee', 'espresso', '', 'espresso', 'Coffee Beans']);

    expect($deduped)->toBe(['espresso', 'Coffee Beans']);
});

it('runs keyword-specific checks for each related keyphrase', function (): void {
    $analyzer = new KeyphraseAnalyzer;

    $context = new ContentContext(
        title: 'Best Coffee Beans for Espresso — The Complete Buying Guide',
        description: 'A guide to the best coffee beans for espresso.',
        slug: 'best-coffee-beans-for-espresso',
        contentHtml: '<p>Espresso brewing starts with choosing the best coffee beans for espresso.</p><h2>Espresso beans matter</h2><p>More text here.</p>',
        focusKeyphrase: 'best coffee beans for espresso',
        relatedKeyphrases: ['espresso beans'],
    );

    $results = $analyzer->analyzeRelated($context);

    expect($results)->toHaveKey('espresso beans')
        ->and($results['espresso beans'])->not->toBeEmpty();
});
