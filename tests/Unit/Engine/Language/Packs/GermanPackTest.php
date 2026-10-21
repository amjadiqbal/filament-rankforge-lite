<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Language\Packs\GermanPack;

it('reports the de locale, is not RTL, and supports syllable counting', function (): void {
    $pack = new GermanPack;

    expect($pack->locale())->toBe('de')
        ->and($pack->isRtl())->toBeFalse()
        ->and($pack->supportsSyllableCounting())->toBeTrue();
});

it('exposes a non-empty transition word list', function (): void {
    expect((new GermanPack)->transitionWords())->toContain('jedoch', 'daher', 'zusammenfassend');
});

it('exposes passive-voice auxiliary indicators', function (): void {
    expect((new GermanPack)->passiveIndicators())->toContain('wird', 'wurde', 'worden');
});

it('counts vowel-group syllables', function (): void {
    $pack = new GermanPack;

    expect($pack->countSyllables('Haus'))->toBe(1)
        ->and($pack->countSyllables(''))->toBe(0)
        ->and($pack->countSyllables('schön'))->toBe(1);
});

it('exposes Flesch reading ease coefficients', function (): void {
    expect((new GermanPack)->fleschCoefficients())->toBe([
        'base' => 180.0,
        'wordsPerSentence' => 1.0,
        'syllablesPerWord' => 58.5,
    ]);
});
