<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Language\AbstractLanguagePack;
use AmjadIqbal\FilamentRankForge\Engine\Language\LanguageRegistry;

it('ships 6 language packs by default', function (): void {
    $registry = new LanguageRegistry;

    foreach (['en', 'es', 'fr', 'de', 'ar', 'ur'] as $locale) {
        expect($registry->has($locale))->toBeTrue();
    }
});

it('flags Arabic and Urdu as RTL and syllable-counting-free', function (): void {
    $registry = new LanguageRegistry;

    expect($registry->get('ar')->isRtl())->toBeTrue()
        ->and($registry->get('ur')->isRtl())->toBeTrue()
        ->and($registry->get('ar')->supportsSyllableCounting())->toBeFalse()
        ->and($registry->get('ur')->supportsSyllableCounting())->toBeFalse()
        ->and($registry->get('en')->isRtl())->toBeFalse();
});

it('falls back to English for an unknown locale', function (): void {
    expect((new LanguageRegistry)->get('xx')->locale())->toBe('en');
});

it('allows registering a custom language pack', function (): void {
    $italian = new class extends AbstractLanguagePack
    {
        public function locale(): string
        {
            return 'it';
        }

        public function transitionWords(): array
        {
            return ['tuttavia'];
        }

        public function passiveIndicators(): array
        {
            return ['è'];
        }

        public function countSyllables(string $word): int
        {
            return 1;
        }

        public function fleschCoefficients(): array
        {
            return ['base' => 206.835, 'wordsPerSentence' => 1.015, 'syllablesPerWord' => 84.6];
        }
    };

    $registry = new LanguageRegistry;
    $registry->register($italian);

    expect($registry->has('it'))->toBeTrue()
        ->and($registry->get('it')->locale())->toBe('it');
});
