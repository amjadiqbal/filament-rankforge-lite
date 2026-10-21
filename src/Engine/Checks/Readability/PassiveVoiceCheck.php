<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Readability;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Language\LanguageRegistry;
use AmjadIqbal\FilamentRankForge\Engine\Status;

/**
 * Heuristic passive-voice detector: an auxiliary "to be" verb followed
 * within a few words by a token that looks like a past participle.
 * Locales whose language pack returns no passiveIndicators() (Arabic,
 * Urdu — passive voice is morphological there) always report 0%.
 */
final class PassiveVoiceCheck implements Check
{
    /**
     * @var array<string, string[]>
     */
    private const PARTICIPLE_SUFFIXES = [
        'en' => ['ed', 'en'],
        'es' => ['ado', 'ada', 'ados', 'adas', 'ido', 'ida', 'idos', 'idas'],
        'fr' => ['é', 'ée', 'és', 'ées'],
    ];

    public function __construct(
        private readonly LanguageRegistry $languages = new LanguageRegistry,
        private readonly float $goodMaxPercent = 10.0,
        private readonly float $okMaxPercent = 15.0,
        private readonly int $weight = 5,
    ) {}

    public function id(): string
    {
        return 'passive-voice';
    }

    public function group(): string
    {
        return 'readability';
    }

    public function weight(): int
    {
        return $this->weight;
    }

    public function isApplicable(ContentContext $context): bool
    {
        return $context->extracted()->sentences !== [];
    }

    public function run(ContentContext $context): CheckResult
    {
        $sentences = $context->extracted()->sentences;
        $auxiliaries = array_map(
            fn (string $word): string => mb_strtolower($word),
            $this->languages->get($context->language)->passiveIndicators(),
        );

        $passiveCount = 0;
        foreach ($sentences as $sentence) {
            if (self::isPassive($sentence, $auxiliaries, $context->language)) {
                $passiveCount++;
            }
        }

        $percent = round(($passiveCount / count($sentences)) * 100, 1);

        $status = match (true) {
            $percent <= $this->goodMaxPercent => Status::Good,
            $percent <= $this->okMaxPercent => Status::Ok,
            default => Status::Bad,
        };

        $message = match ($status) {
            Status::Good => "{$percent}% of sentences use passive voice — within the target.",
            Status::Ok => "{$percent}% of sentences use passive voice — consider using active voice more.",
            Status::Bad => "{$percent}% of sentences use passive voice — too much passive voice.",
        };

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }

    /**
     * @param  string[]  $auxiliaries
     */
    private static function isPassive(string $sentence, array $auxiliaries, string $locale): bool
    {
        if ($auxiliaries === []) {
            return false;
        }

        $cleaned = preg_replace('/[^\p{L}\p{N}\s]/u', '', $sentence) ?? '';
        $words = preg_split('/\s+/u', mb_strtolower($cleaned), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($words as $index => $word) {
            if (! in_array($word, $auxiliaries, true)) {
                continue;
            }

            for ($lookahead = 1; $lookahead <= 3; $lookahead++) {
                $next = $words[$index + $lookahead] ?? null;

                if ($next === null) {
                    break;
                }

                if (self::looksLikeParticiple($next, $locale)) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function looksLikeParticiple(string $word, string $locale): bool
    {
        if ($locale === 'de') {
            return str_starts_with($word, 'ge') && mb_strlen($word) > 3;
        }

        $suffixes = self::PARTICIPLE_SUFFIXES[$locale] ?? self::PARTICIPLE_SUFFIXES['en'];

        foreach ($suffixes as $suffix) {
            if (str_ends_with($word, $suffix)) {
                return true;
            }
        }

        return false;
    }
}
