<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\ImageAltCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is Ok when there are no images at all', function (): void {
    $context = new ContentContext(contentHtml: '<p>just text here</p>');

    expect((new ImageAltCheck)->run($context)->status)->toBe(Status::Ok);
});

it('is Ok with partial alt-text coverage', function (): void {
    $context = new ContentContext(
        contentHtml: '<p>text</p><img src="/a.jpg" alt="a photo" /><img src="/b.jpg" alt="" />',
    );

    expect((new ImageAltCheck)->run($context)->status)->toBe(Status::Ok);
});

it('is Bad when no image has alt text', function (): void {
    $context = new ContentContext(
        contentHtml: '<p>text</p><img src="/a.jpg" alt="" /><img src="/b.jpg" alt="" />',
    );

    expect((new ImageAltCheck)->run($context)->status)->toBe(Status::Bad);
});

it('is Good when every image has alt text (regression: int/float divide)', function (): void {
    $context = new ContentContext(
        contentHtml: '<p>text</p><img src="/a.jpg" alt="a descriptive photo" />',
    );

    expect((new ImageAltCheck)->run($context)->status)->toBe(Status::Good);
});
