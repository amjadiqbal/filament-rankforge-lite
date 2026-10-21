<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Language\Packs;

use AmjadIqbal\FilamentRankForge\Engine\Language\AbstractLanguagePack;

final class ArabicPack extends AbstractLanguagePack
{
    public function locale(): string
    {
        return 'ar';
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
            'لكن', 'لذلك', 'بالإضافة إلى ذلك', 'علاوة على ذلك', 'مع ذلك',
            'في الواقع', 'على سبيل المثال', 'نتيجة لذلك', 'بالمقابل', 'بالمثل',
            'أولاً', 'ثانياً', 'أخيراً', 'في الختام', 'من ناحية أخرى',
            'بعبارة أخرى', 'باختصار',
        ];
    }

    /**
     * Arabic passive voice is morphological (مبني للمجهول), not formed with an
     * auxiliary verb, so there is no reliable word list to match against.
     * The passive-voice check will report 0% for this pack until a
     * morphological analyzer is available.
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
