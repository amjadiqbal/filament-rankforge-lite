<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Readability\FleschReadingEaseCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is not applicable for languages that skip syllable counting', function (): void {
    $context = new ContentContext(contentHtml: '<p>مرحبا بكم في هذا المقال</p>', language: 'ar');

    expect((new FleschReadingEaseCheck)->isApplicable($context))->toBeFalse();
});

it('is Bad for dense, complex sentences', function (): void {
    $html = '<p>'.str_repeat('Extraordinarily sophisticated international communication responsibility organizations frequently necessitate unprecedented multidimensional collaborative methodologies. ', 4).'</p>';

    expect((new FleschReadingEaseCheck)->run(new ContentContext(contentHtml: $html))->status)->toBe(Status::Bad);
});

it('is Good for short, simple sentences', function (): void {
    $html = '<p>'.str_repeat('The cat sat on the mat. It was a warm day. She had a nice cup of tea. ', 6).'</p>';

    expect((new FleschReadingEaseCheck)->run(new ContentContext(contentHtml: $html))->status)->toBe(Status::Good);
});
