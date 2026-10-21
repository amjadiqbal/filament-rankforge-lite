<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine;

final readonly class Report
{
    /**
     * @param  CheckResult[]  $results
     */
    public function __construct(public array $results) {}

    public function score(): ?int
    {
        return self::scoreFor($this->results);
    }

    public function seoScore(): ?int
    {
        return self::scoreFor($this->resultsIn('seo'));
    }

    public function readabilityScore(): ?int
    {
        return self::scoreFor($this->resultsIn('readability'));
    }

    public function rating(): ?Rating
    {
        $score = $this->score();

        return $score === null ? null : Rating::fromScore($score);
    }

    /**
     * @return CheckResult[]
     */
    public function resultsIn(string $group): array
    {
        return array_values(array_filter(
            $this->results,
            fn (CheckResult $result): bool => $result->group === $group,
        ));
    }

    /**
     * @return CheckResult[]
     */
    public function passed(): array
    {
        return $this->byStatus(Status::Good);
    }

    /**
     * @return CheckResult[]
     */
    public function warnings(): array
    {
        return $this->byStatus(Status::Ok);
    }

    /**
     * @return CheckResult[]
     */
    public function failed(): array
    {
        return $this->byStatus(Status::Bad);
    }

    /**
     * @return CheckResult[]
     */
    private function byStatus(Status $status): array
    {
        return array_values(array_filter(
            $this->results,
            fn (CheckResult $result): bool => $result->status === $status,
        ));
    }

    /**
     * @param  CheckResult[]  $results
     */
    private static function scoreFor(array $results): ?int
    {
        $totalWeight = array_sum(array_map(fn (CheckResult $result): int => $result->weight, $results));

        if ($totalWeight === 0) {
            return null;
        }

        $earned = array_sum(array_map(
            fn (CheckResult $result): float => $result->weight * self::multiplier($result->status),
            $results,
        ));

        return (int) round($earned / $totalWeight * 100);
    }

    private static function multiplier(Status $status): float
    {
        return match ($status) {
            Status::Good => 1.0,
            Status::Ok => 0.5,
            Status::Bad => 0.0,
        };
    }
}
