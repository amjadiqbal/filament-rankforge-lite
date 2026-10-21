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
     * @return Check[]
     */
    public static function defaults(): array
    {
        return [
            new TitleLengthCheck,
            new TitleKeywordCheck,
            new MetaDescriptionLengthCheck,
            new MetaDescriptionKeywordCheck,
            new SlugKeywordCheck,
            new SlugLengthCheck,
            new FirstParagraphKeywordCheck,
            new SubheadingsKeywordCheck,
            new KeywordDensityCheck,
            new ContentLengthCheck,
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
