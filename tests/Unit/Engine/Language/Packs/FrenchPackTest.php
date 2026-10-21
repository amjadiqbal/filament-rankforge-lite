<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Language\Packs\FrenchPack;

it('reports the fr locale, is not RTL, and supports syllable counting', function (): void {
    $pack = new FrenchPack;

    expect($pack->locale())->toBe('fr')
        ->and($pack->isRtl())->toBeFalse()
        ->and($pack->supportsSyllableCounting())->toBeTrue();
});

it('exposes a non-empty transition word list', function (): void {
    expect((new FrenchPack)->transitionWords())->toContain('cependant', 'donc', 'en conclusion');
});

it('exposes passive-voice auxiliary indicators', function (): void {
    expect((new FrenchPack)->passiveIndicators())->toContain('est', 'était', 'été');
});

it('counts vowel-group syllables', function (): void {
    $pack = new FrenchPack;

    expect($pack->countSyllables('bonjour'))->toBe(2)
        ->and($pack->countSyllables(''))->toBe(0)
        ->and($pack->countSyllables('château'))->toBe(2);
});

it('exposes Flesch reading ease coefficients', function (): void {
    expect((new FrenchPack)->fleschCoefficients())->toBe([
        'base' => 207.0,
        'wordsPerSentence' => 1.015,
        'syllablesPerWord' => 73.6,
    ]);
});
