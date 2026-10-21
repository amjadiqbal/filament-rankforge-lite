<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\ContentLengthCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is Bad when the content is far too short', function (): void {
    $context = new ContentContext(contentHtml: '<p>'.str_repeat('word ', 50).'</p>');

    expect((new ContentLengthCheck)->run($context)->status)->toBe(Status::Bad);
});

it('is Ok in the mid range', function (): void {
    $context = new ContentContext(contentHtml: '<p>'.str_repeat('word ', 400).'</p>');

    expect((new ContentLengthCheck)->run($context)->status)->toBe(Status::Ok);
});

it('is Good at 600+ words', function (): void {
    $context = new ContentContext(contentHtml: '<p>'.str_repeat('word ', 700).'</p>');

    expect((new ContentLengthCheck)->run($context)->status)->toBe(Status::Good);
});
