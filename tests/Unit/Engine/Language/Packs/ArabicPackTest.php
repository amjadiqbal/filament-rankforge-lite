<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Language\Packs\ArabicPack;

it('reports the ar locale, is RTL, and does not support syllable counting', function (): void {
    $pack = new ArabicPack;

    expect($pack->locale())->toBe('ar')
        ->and($pack->isRtl())->toBeTrue()
        ->and($pack->supportsSyllableCounting())->toBeFalse();
});

it('exposes a non-empty transition word list', function (): void {
    expect((new ArabicPack)->transitionWords())->not->toBeEmpty();
});

it('has no passive-voice indicators, since Arabic passive is morphological', function (): void {
    expect((new ArabicPack)->passiveIndicators())->toBe([]);
});

it('falls back countSyllables() to a character-count heuristic (never invoked in practice)', function (): void {
    $pack = new ArabicPack;

    expect($pack->countSyllables('مرحبا'))->toBe(5)
        ->and($pack->countSyllables(''))->toBe(1);
});

it('exposes Flesch reading ease coefficients', function (): void {
    expect((new ArabicPack)->fleschCoefficients())->toBe([
        'base' => 206.835,
        'wordsPerSentence' => 1.015,
        'syllablesPerWord' => 84.6,
    ]);
});
