<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Language\Packs;

use AmjadIqbal\FilamentRankForge\Engine\Language\AbstractLanguagePack;

final class EnglishPack extends AbstractLanguagePack
{
    private const VOWELS = 'aeiouy';

    public function locale(): string
    {
        return 'en';
    }

    public function transitionWords(): array
    {
        return [
            'however', 'therefore', 'moreover', 'furthermore', 'additionally',
            'consequently', 'meanwhile', 'nevertheless', 'in addition', 'for example',
            'as a result', 'in contrast', 'similarly', 'in fact', 'for instance',
            'first', 'second', 'finally', 'in conclusion', 'on the other hand',
            'in other words', 'accordingly', 'likewise', 'in summary', 'thus',
        ];
    }

    public function passiveIndicators(): array
    {
        return ['is', 'are', 'was', 'were', 'be', 'been', 'being'];
    }

    public function countSyllables(string $word): int
    {
        $word = mb_strtolower(trim($word));

        if ($word === '') {
            return 0;
        }

        $syllables = $this->countVowelGroupSyllables($word, self::VOWELS);

        if (mb_strlen($word) > 2 && str_ends_with($word, 'e') && ! str_ends_with($word, 'le')) {
            $syllables = max(1, $syllables - 1);
        }

        return $syllables;
    }

    public function fleschCoefficients(): array
    {
        return ['base' => 206.835, 'wordsPerSentence' => 1.015, 'syllablesPerWord' => 84.6];
    }
}
