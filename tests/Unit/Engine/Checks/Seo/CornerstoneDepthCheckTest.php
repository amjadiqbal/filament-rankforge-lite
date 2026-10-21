<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\CornerstoneDepthCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is not applicable unless cornerstone is flagged', function (): void {
    $context = new ContentContext(contentHtml: '<p>word</p>', cornerstone: false);

    expect((new CornerstoneDepthCheck)->isApplicable($context))->toBeFalse();
});

it('is Bad for shallow cornerstone content', function (): void {
    $context = new ContentContext(
        contentHtml: '<p>'.str_repeat('word ', 100).'</p><h2>One</h2>',
        cornerstone: true,
    );

    expect((new CornerstoneDepthCheck)->run($context)->status)->toBe(Status::Bad);
});

it('is Good at 900+ words with 3+ subheadings', function (): void {
    $html = '<h2>A</h2><p>'.str_repeat('word ', 300).'</p>'
        .'<h2>B</h2><p>'.str_repeat('word ', 300).'</p>'
        .'<h2>C</h2><p>'.str_repeat('word ', 300).'</p>';

    $context = new ContentContext(contentHtml: $html, cornerstone: true);

    expect((new CornerstoneDepthCheck)->run($context)->status)->toBe(Status::Good);
});
