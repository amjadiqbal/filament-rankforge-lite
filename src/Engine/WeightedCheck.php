<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine;

/**
 * Wraps a check and reports a different weight, both through weight() and on the
 * CheckResult it produces. Used for config('rankforge.analysis.weights_override').
 */
final class WeightedCheck implements Check
{
    public function __construct(private readonly Check $inner, private readonly int $weight) {}

    public function id(): string
    {
        return $this->inner->id();
    }

    public function group(): string
    {
        return $this->inner->group();
    }

    public function weight(): int
    {
        return $this->weight;
    }

    public function isApplicable(ContentContext $context): bool
    {
        return $this->inner->isApplicable($context);
    }

    public function run(ContentContext $context): CheckResult
    {
        $result = $this->inner->run($context);

        return new CheckResult($result->id, $result->group, $result->status, $result->message, $this->weight);
    }
}
