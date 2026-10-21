<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Readability\ParagraphLengthCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is Good when no paragraph exceeds 150 words', function (): void {
    $context = new ContentContext(contentHtml: '<p>A short paragraph.</p>');

    expect((new ParagraphLengthCheck)->run($context)->status)->toBe(Status::Good);
});

it('is Bad for a single paragraph well over 150 words', function (): void {
    $context = new ContentContext(contentHtml: '<p>'.str_repeat('word ', 200).'</p>');

    expect((new ParagraphLengthCheck)->run($context)->status)->toBe(Status::Bad);
});
