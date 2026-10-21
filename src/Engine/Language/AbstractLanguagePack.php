<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Language;

abstract class AbstractLanguagePack implements LanguagePack
{
    public function isRtl(): bool
    {
        return false;
    }

    public function supportsSyllableCounting(): bool
    {
        return true;
    }

    /**
     * Heuristic vowel-group syllable counter shared by Latin-script packs.
     * Not linguistically exact, but consistent and good enough for readability scoring.
     */
    protected function countVowelGroupSyllables(string $word, string $vowels): int
    {
        $word = mb_strtolower(trim($word));

        if ($word === '') {
            return 0;
        }

        preg_match_all('/['.$vowels.']+/u', $word, $matches);

        return max(1, count($matches[0]));
    }
}
