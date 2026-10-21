<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Language\Packs;

use AmjadIqbal\FilamentRankForge\Engine\Language\AbstractLanguagePack;

final class SpanishPack extends AbstractLanguagePack
{
    private const VOWELS = 'aeiouáéíóúü';

    public function locale(): string
    {
        return 'es';
    }

    public function transitionWords(): array
    {
        return [
            'sin embargo', 'por lo tanto', 'además', 'en consecuencia', 'mientras tanto',
            'no obstante', 'por ejemplo', 'como resultado', 'en cambio', 'de manera similar',
            'de hecho', 'primero', 'segundo', 'finalmente', 'en conclusión',
            'por otro lado', 'en otras palabras', 'en resumen', 'así que', 'asimismo',
        ];
    }

    public function passiveIndicators(): array
    {
        return ['es', 'son', 'fue', 'fueron', 'era', 'eran', 'sido', 'siendo', 'está', 'están', 'estaba', 'estaban'];
    }

    public function countSyllables(string $word): int
    {
        return $this->countVowelGroupSyllables($word, self::VOWELS);
    }

    public function fleschCoefficients(): array
    {
        return ['base' => 206.84, 'wordsPerSentence' => 1.02, 'syllablesPerWord' => 60.0];
    }
}
