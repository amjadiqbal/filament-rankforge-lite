<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine;

interface Check
{
    public function id(): string;

    public function group(): string;

    public function weight(): int;

    public function isApplicable(ContentContext $context): bool;

    public function run(ContentContext $context): CheckResult;
}
