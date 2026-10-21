<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Seo;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class OutboundLinksCheck implements Check
{
    public function __construct(private readonly int $weight = 4) {}

    public function id(): string
    {
        return 'outbound-links';
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

        $outboundCount = 0;
        foreach ($extracted->links as $link) {
            if (self::isOutbound($link['href'], is_string($canonicalHost) ? $canonicalHost : null)) {
                $outboundCount++;
            }
        }

        $status = $outboundCount > 0 ? Status::Good : Status::Bad;
        $message = $outboundCount > 0
            ? "Found {$outboundCount} outbound link(s)."
            : 'No outbound links were found in the content.';

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }

    private static function isOutbound(string $href, ?string $canonicalHost): bool
    {
        $href = trim($href);

        if ($href === '' || ! preg_match('#^https?://#i', $href)) {
            return false;
        }

        $linkHost = parse_url($href, PHP_URL_HOST);

        if (! is_string($linkHost)) {
            return false;
        }

        return $canonicalHost === null || strcasecmp($linkHost, $canonicalHost) !== 0;
    }
}
