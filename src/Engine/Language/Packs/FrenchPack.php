<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Language\Packs;

use AmjadIqbal\FilamentRankForge\Engine\Language\AbstractLanguagePack;

final class FrenchPack extends AbstractLanguagePack
{
    private const VOWELS = 'aeiouyàâäéèêëîïôöùûüÿ';

    public function locale(): string
    {
        return 'fr';
    }

    public function transitionWords(): array
    {
        return [
            'cependant', 'donc', 'de plus', 'par conséquent', 'en outre',
            'entre-temps', 'néanmoins', 'par exemple', 'en revanche', 'de même',
            'en fait', 'premièrement', 'deuxièmement', 'enfin', 'en conclusion',
            "d'autre part", "en d'autres termes", 'en résumé', 'ainsi', 'toutefois',
        ];
    }

    public function passiveIndicators(): array
    {
        return ['est', 'sont', 'était', 'étaient', 'fut', 'furent', 'été', 'étant'];
    }

    public function countSyllables(string $word): int
    {
        return $this->countVowelGroupSyllables($word, self::VOWELS);
    }

    public function fleschCoefficients(): array
    {
        return ['base' => 207.0, 'wordsPerSentence' => 1.015, 'syllablesPerWord' => 73.6];
    }
}
