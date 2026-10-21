<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Language\Packs;

use AmjadIqbal\FilamentRankForge\Engine\Language\AbstractLanguagePack;

final class GermanPack extends AbstractLanguagePack
{
    private const VOWELS = 'aeiouyäöü';

    public function locale(): string
    {
        return 'de';
    }

    public function transitionWords(): array
    {
        return [
            'jedoch', 'daher', 'außerdem', 'folglich', 'inzwischen',
            'dennoch', 'zum beispiel', 'im gegensatz dazu', 'ebenso', 'tatsächlich',
            'erstens', 'zweitens', 'schließlich', 'zusammenfassend', 'andererseits',
            'mit anderen worten', 'kurz gesagt', 'somit', 'trotzdem',
        ];
    }

    public function passiveIndicators(): array
    {
        return ['wird', 'werden', 'wurde', 'wurden', 'worden', 'ist', 'sind', 'war', 'waren'];
    }

    public function countSyllables(string $word): int
    {
        return $this->countVowelGroupSyllables($word, self::VOWELS);
    }

    public function fleschCoefficients(): array
    {
        return ['base' => 180.0, 'wordsPerSentence' => 1.0, 'syllablesPerWord' => 58.5];
    }
}
