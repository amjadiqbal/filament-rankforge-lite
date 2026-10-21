<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine;

final readonly class CheckResult
{
    public function __construct(
        public string $id,
        public string $group,
        public Status $status,
        public string $message,
        public int $weight,
    ) {}
}
