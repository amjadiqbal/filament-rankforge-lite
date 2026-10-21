<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Language\Packs\UrduPack;

it('reports the ur locale, is RTL, and does not support syllable counting', function (): void {
    $pack = new UrduPack;

    expect($pack->locale())->toBe('ur')
        ->and($pack->isRtl())->toBeTrue()
        ->and($pack->supportsSyllableCounting())->toBeFalse();
});

it('exposes a non-empty transition word list', function (): void {
    expect((new UrduPack)->transitionWords())->not->toBeEmpty();
});

it('has no passive-voice indicators, since Urdu passive is morphological', function (): void {
    expect((new UrduPack)->passiveIndicators())->toBe([]);
});

it('falls back countSyllables() to a character-count heuristic (never invoked in practice)', function (): void {
    $pack = new UrduPack;

    expect($pack->countSyllables('خوش'))->toBe(3)
        ->and($pack->countSyllables(''))->toBe(1);
});

it('exposes Flesch reading ease coefficients', function (): void {
    expect((new UrduPack)->fleschCoefficients())->toBe([
        'base' => 206.835,
        'wordsPerSentence' => 1.015,
        'syllablesPerWord' => 84.6,
    ]);
});
