<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Language\Packs\SpanishPack;

it('reports the es locale, is not RTL, and supports syllable counting', function (): void {
    $pack = new SpanishPack;

    expect($pack->locale())->toBe('es')
        ->and($pack->isRtl())->toBeFalse()
        ->and($pack->supportsSyllableCounting())->toBeTrue();
});

it('exposes a non-empty transition word list', function (): void {
    expect((new SpanishPack)->transitionWords())->toContain('sin embargo', 'por lo tanto', 'en conclusión');
});

it('exposes passive-voice auxiliary indicators', function (): void {
    expect((new SpanishPack)->passiveIndicators())->toContain('es', 'fueron', 'está');
});

it('counts vowel-group syllables', function (): void {
    $pack = new SpanishPack;

    expect($pack->countSyllables('casa'))->toBe(2)
        ->and($pack->countSyllables(''))->toBe(0)
        ->and($pack->countSyllables('café'))->toBe(2);
});

it('exposes Flesch reading ease coefficients', function (): void {
    expect((new SpanishPack)->fleschCoefficients())->toBe([
        'base' => 206.84,
        'wordsPerSentence' => 1.02,
        'syllablesPerWord' => 60.0,
    ]);
});
