<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Readability\SentenceLengthCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is Good when no sentences exceed 20 words', function (): void {
    $context = new ContentContext(contentHtml: '<p>Short sentence one. Short sentence two.</p>');

    expect((new SentenceLengthCheck)->run($context)->status)->toBe(Status::Good);
});

it('is Bad when all sentences exceed 20 words', function (): void {
    $html = '<p>'.str_repeat('This is a fairly long and deliberately wordy sentence with plenty of extra words in it just to push it well past the twenty word threshold easily. ', 6).'</p>';

    expect((new SentenceLengthCheck)->run(new ContentContext(contentHtml: $html))->status)->toBe(Status::Bad);
});
