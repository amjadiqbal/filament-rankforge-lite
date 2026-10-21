<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\InternalLinksCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is Bad with no internal links', function (): void {
    $context = new ContentContext(contentHtml: '<p>'.str_repeat('word ', 100).'</p>');

    expect((new InternalLinksCheck)->run($context)->status)->toBe(Status::Bad);
});

it('is Ok when below the target ratio', function (): void {
    $context = new ContentContext(contentHtml: '<p>'.str_repeat('word ', 1000).' <a href="/a">a</a></p>');

    expect((new InternalLinksCheck)->run($context)->status)->toBe(Status::Ok);
});

it('is Good when meeting the target ratio', function (): void {
    $context = new ContentContext(contentHtml: '<p>'.str_repeat('word ', 100).' <a href="/a">a</a></p>');

    expect((new InternalLinksCheck)->run($context)->status)->toBe(Status::Good);
});

it('treats relative and same-host absolute links as internal', function (): void {
    $context = new ContentContext(
        contentHtml: '<p>text <a href="https://example.com/other">a</a></p>',
        canonicalUrl: 'https://example.com/page',
    );

    expect((new InternalLinksCheck)->run($context)->status)->toBe(Status::Good);
});
