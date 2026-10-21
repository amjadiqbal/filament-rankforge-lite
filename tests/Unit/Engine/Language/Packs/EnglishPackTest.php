<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Language\Packs\EnglishPack;

it('reports the en locale, is not RTL, and supports syllable counting', function (): void {
    $pack = new EnglishPack;

    expect($pack->locale())->toBe('en')
        ->and($pack->isRtl())->toBeFalse()
        ->and($pack->supportsSyllableCounting())->toBeTrue();
});

it('exposes a non-empty transition word list', function (): void {
    expect((new EnglishPack)->transitionWords())->toContain('however', 'therefore', 'in conclusion');
});

it('exposes passive-voice auxiliary indicators', function (): void {
    expect((new EnglishPack)->passiveIndicators())->toContain('is', 'was', 'been');
});

it('counts syllables for simple words', function (): void {
    $pack = new EnglishPack;

    expect($pack->countSyllables('cat'))->toBe(1)
        ->and($pack->countSyllables('banana'))->toBe(3)
        ->and($pack->countSyllables(''))->toBe(0);
});

it('drops a silent trailing e when counting syllables', function (): void {
    $pack = new EnglishPack;

    expect($pack->countSyllables('take'))->toBe(1)
        ->and($pack->countSyllables('able'))->toBe(2);
});

it('exposes Flesch reading ease coefficients', function (): void {
    expect((new EnglishPack)->fleschCoefficients())->toBe([
        'base' => 206.835,
        'wordsPerSentence' => 1.015,
        'syllablesPerWord' => 84.6,
    ]);
});
