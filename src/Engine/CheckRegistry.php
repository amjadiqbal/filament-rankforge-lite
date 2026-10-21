<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine;

use AmjadIqbal\FilamentRankForge\Engine\Checks\Readability\ConsecutiveSentenceStartsCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Readability\FleschReadingEaseCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Readability\ParagraphLengthCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Readability\PassiveVoiceCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Readability\SentenceLengthCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Readability\SubheadingDistributionCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Readability\TransitionWordsCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Readability\WordComplexityCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\ContentLengthCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\CornerstoneDepthCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\FirstParagraphKeywordCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\ImageAltCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\InternalLinksCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\KeywordDensityCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\MetaDescriptionKeywordCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\MetaDescriptionLengthCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\OutboundLinksCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\SlugKeywordCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\SlugLengthCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\SubheadingsKeywordCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\TitleKeywordCheck;
use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\TitleLengthCheck;

final class CheckRegistry
{
    /**
     * @var Check[]
     */
    private array $checks;

    /**
     * @param  Check[]|null  $checks
     */
    public function __construct(?array $checks = null)
    {
        $this->checks = $checks ?? self::defaults();
    }

    /**
     * The built-in checks, tuned by the `rankforge.analysis` config:
     *
     *  - disabled_checks:       list of check ids to leave out, e.g. ['passive-voice'].
     *  - weights_override:      check id => integer weight (0 or more), e.g. ['title-length' => 5].
     *  - keyword_density_range: [min, max] percent that counts as ideal keyword density.
     *  - content_length_tiers:  [ok, good, ...] word counts; the first two set the thresholds.
     *
     * Pass an array to override the config (the engine itself needs no framework: with no
     * Laravel config available the built-in defaults are used).
     *
     * @param  array<string, mixed>|null  $config
     * @return Check[]
     */
    public static function defaults(?array $config = null): array
    {
        $config ??= self::configuredAnalysis();

        [$densityMin, $densityMax] = self::densityRange($config['keyword_density_range'] ?? null);
        [$lengthOk, $lengthGood] = self::lengthTiers($config['content_length_tiers'] ?? null);

        $checks = [
            new TitleLengthCheck,
            new TitleKeywordCheck,
            new MetaDescriptionLengthCheck,
            new MetaDescriptionKeywordCheck,
            new SlugKeywordCheck,
            new SlugLengthCheck,
            new FirstParagraphKeywordCheck,
            new SubheadingsKeywordCheck,
            new KeywordDensityCheck(goodMin: $densityMin, goodMax: $densityMax),
            new ContentLengthCheck(okThreshold: $lengthOk, goodThreshold: $lengthGood),
            new InternalLinksCheck,
            new OutboundLinksCheck,
            new ImageAltCheck,
            new CornerstoneDepthCheck,
            new FleschReadingEaseCheck,
            new SentenceLengthCheck,
            new ParagraphLengthCheck,
            new PassiveVoiceCheck,
            new TransitionWordsCheck,
            new SubheadingDistributionCheck,
            new ConsecutiveSentenceStartsCheck,
            new WordComplexityCheck,
        ];

        $disabled = array_map('strval', array_filter((array) ($config['disabled_checks'] ?? []), 'is_scalar'));
        $weights = [];

        foreach ((array) ($config['weights_override'] ?? []) as $id => $weight) {
            if (is_numeric($weight) && (int) $weight >= 0) {
                $weights[(string) $id] = (int) $weight;
            }
        }

        $configured = [];

        foreach ($checks as $check) {
            if (in_array($check->id(), $disabled, true)) {
                continue;
            }

            $configured[] = isset($weights[$check->id()]) ? new WeightedCheck($check, $weights[$check->id()]) : $check;
        }

        return $configured;
    }

    /**
     * @return array<string, mixed>
     */
    private static function configuredAnalysis(): array
    {
        if (! function_exists('config')) {
            return [];
        }

        try {
            $config = config('rankforge.analysis');
        } catch (\Throwable) {
            return [];
        }

        return is_array($config) ? $config : [];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private static function densityRange(mixed $range): array
    {
        $default = [0.5, 2.5];

        if (! is_array($range) || ! isset($range[0], $range[1]) || ! is_numeric($range[0]) || ! is_numeric($range[1])) {
            return $default;
        }

        $min = (float) $range[0];
        $max = (float) $range[1];

        return ($min >= 0 && $max >= $min) ? [$min, $max] : $default;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private static function lengthTiers(mixed $tiers): array
    {
        $default = [300, 600];

        if (! is_array($tiers)) {
            return $default;
        }

        $tiers = array_values($tiers);

        if (! isset($tiers[0], $tiers[1]) || ! is_numeric($tiers[0]) || ! is_numeric($tiers[1])) {
            return $default;
        }

        $ok = (int) $tiers[0];
        $good = (int) $tiers[1];

        return ($ok > 0 && $good >= $ok) ? [$ok, $good] : $default;
    }

    public function withCheck(Check $check): self
    {
        $checks = $this->checks;
        $checks[] = $check;

        return new self($checks);
    }

    public function withoutCheck(string $id): self
    {
        return new self(array_values(array_filter(
            $this->checks,
            fn (Check $check): bool => $check->id() !== $id,
        )));
    }

    /**
     * @param  string[]  $ids
     */
    public function withoutChecks(array $ids): self
    {
        return new self(array_values(array_filter(
            $this->checks,
            fn (Check $check): bool => ! in_array($check->id(), $ids, true),
        )));
    }

    /**
     * @return Check[]
     */
    public function all(): array
    {
        return $this->checks;
    }
}
