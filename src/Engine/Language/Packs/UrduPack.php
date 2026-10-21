<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Language\Packs;

use AmjadIqbal\FilamentRankForge\Engine\Language\AbstractLanguagePack;

final class UrduPack extends AbstractLanguagePack
{
    public function locale(): string
    {
        return 'ur';
    }

    public function isRtl(): bool
    {
        return true;
    }

    public function supportsSyllableCounting(): bool
    {
        return false;
    }

    public function transitionWords(): array
    {
        return [
            'لیکن', 'اس لیے', 'مزید برآں', 'اس کے علاوہ', 'تاہم',
            'دراصل', 'مثال کے طور پر', 'نتیجتاً', 'اس کے برعکس', 'اسی طرح',
            'پہلا', 'دوسرا', 'آخر میں', 'آخرکار', 'دوسری طرف',
            'دوسرے الفاظ میں', 'خلاصہ یہ کہ',
        ];
    }

    /**
     * Urdu passive voice is formed morphologically (verb stem + جانا), not
     * with a fixed auxiliary word list, so there is nothing reliable to
     * match against. The passive-voice check reports 0% for this pack until
     * a morphological analyzer is available.
     */
    public function passiveIndicators(): array
    {
        return [];
    }

    /**
     * Never invoked in practice — supportsSyllableCounting() is false, so
     * syllable-based checks skip this pack via isApplicable().
     */
    public function countSyllables(string $word): int
    {
        return max(1, mb_strlen(trim($word)));
    }

    public function fleschCoefficients(): array
    {
        return ['base' => 206.835, 'wordsPerSentence' => 1.015, 'syllablesPerWord' => 84.6];
    }
}
