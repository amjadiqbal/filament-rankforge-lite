<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Seo;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class InternalLinksCheck implements Check
{
    public function __construct(
        private readonly int $wordsPerLink = 500,
        private readonly int $weight = 5,
    ) {}

    public function id(): string
    {
        return 'internal-links';
    }

    public function group(): string
    {
        return 'seo';
    }

    public function weight(): int
    {
        return $this->weight;
    }

    public function isApplicable(ContentContext $context): bool
    {
        return $context->extracted()->wordCount > 0;
    }

    public function run(ContentContext $context): CheckResult
    {
        $extracted = $context->extracted();
        $canonicalHost = $context->canonicalUrl !== '' ? parse_url($context->canonicalUrl, PHP_URL_HOST) : null;

        $internalCount = 0;
        foreach ($extracted->links as $link) {
            if (self::isInternal($link['href'], is_string($canonicalHost) ? $canonicalHost : null)) {
                $internalCount++;
            }
        }

        $expected = max(1, (int) floor($extracted->wordCount / $this->wordsPerLink));

        $status = match (true) {
            $internalCount >= $expected => Status::Good,
            $internalCount > 0 => Status::Ok,
            default => Status::Bad,
        };

        $message = match ($status) {
            Status::Good => "Found {$internalCount} internal link(s), meeting the target of {$expected}.",
            Status::Ok => "Found {$internalCount} internal link(s), below the target of {$expected}.",
            Status::Bad => 'No internal links were found in the content.',
        };

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }

    private static function isInternal(string $href, ?string $canonicalHost): bool
    {
        $href = trim($href);

        if ($href === '' || str_starts_with($href, '#')) {
            return false;
        }

        if (! preg_match('#^https?://#i', $href)) {
            return true;
        }

        if ($canonicalHost === null) {
            return false;
        }

        $linkHost = parse_url($href, PHP_URL_HOST);

        return is_string($linkHost) && strcasecmp($linkHost, $canonicalHost) === 0;
    }
}
