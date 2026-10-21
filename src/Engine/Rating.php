<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine;

enum Rating: string
{
    case Poor = 'poor';
    case NeedsImprovement = 'needs_improvement';
    case Good = 'good';

    public static function fromScore(int $score): self
    {
        return match (true) {
            $score >= 80 => self::Good,
            $score >= 50 => self::NeedsImprovement,
            default => self::Poor,
        };
    }
}
