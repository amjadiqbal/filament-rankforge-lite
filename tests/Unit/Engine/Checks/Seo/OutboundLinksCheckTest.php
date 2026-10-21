<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\OutboundLinksCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is Bad when only internal links are present', function (): void {
    $context = new ContentContext(
        contentHtml: '<p>text <a href="/internal">link</a></p>',
        canonicalUrl: 'https://example.com/page',
    );

    expect((new OutboundLinksCheck)->run($context)->status)->toBe(Status::Bad);
});

it('is Good when an outbound link to a different host is present', function (): void {
    $context = new ContentContext(
        contentHtml: '<p>text <a href="https://other.example/b">link</a></p>',
        canonicalUrl: 'https://example.com/page',
    );

    expect((new OutboundLinksCheck)->run($context)->status)->toBe(Status::Good);
});
