<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Language;

interface LanguagePack
{
    public function locale(): string;

    public function isRtl(): bool;

    public function supportsSyllableCounting(): bool;

    /**
     * @return string[]
     */
    public function transitionWords(): array;

    /**
     * @return string[]
     */
    public function passiveIndicators(): array;

    public function countSyllables(string $word): int;

    /**
     * @return array{base: float, wordsPerSentence: float, syllablesPerWord: float}
     */
    public function fleschCoefficients(): array;
}
