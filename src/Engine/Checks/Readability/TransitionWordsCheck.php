<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Readability;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Language\LanguageRegistry;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class TransitionWordsCheck implements Check
{
    public function __construct(
        private readonly LanguageRegistry $languages = new LanguageRegistry,
        private readonly float $goodMinPercent = 30.0,
        private readonly float $okMinPercent = 15.0,
        private readonly int $weight = 5,
    ) {}

    public function id(): string
    {
        return 'transition-words';
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
        $transitionWords = $this->languages->get($context->language)->transitionWords();

        $withTransition = 0;
        foreach ($sentences as $sentence) {
            $lower = ' '.mb_strtolower($sentence).' ';
            foreach ($transitionWords as $word) {
                if (mb_stripos($lower, mb_strtolower($word)) !== false) {
                    $withTransition++;
                    break;
                }
            }
        }

        $percent = round(($withTransition / count($sentences)) * 100, 1);

        $status = match (true) {
            $percent >= $this->goodMinPercent => Status::Good,
            $percent >= $this->okMinPercent => Status::Ok,
            default => Status::Bad,
        };

        $message = match ($status) {
            Status::Good => "{$percent}% of sentences contain a transition word — great flow.",
            Status::Ok => "{$percent}% of sentences contain a transition word — add more to improve flow.",
            Status::Bad => "{$percent}% of sentences contain a transition word — too few for good flow.",
        };

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }
}
