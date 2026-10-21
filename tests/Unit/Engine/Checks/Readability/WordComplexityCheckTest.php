<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Readability\WordComplexityCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is not applicable for languages that skip syllable counting', function (): void {
    $context = new ContentContext(contentHtml: '<p>مرحبا بكم في هذا المقال</p>', language: 'ar');

    expect((new WordComplexityCheck)->isApplicable($context))->toBeFalse();
});

it('is Good with mostly simple words', function (): void {
    $context = new ContentContext(contentHtml: '<p>The cat sat on the warm mat all day long.</p>');

    expect((new WordComplexityCheck)->run($context)->status)->toBe(Status::Good);
});

it('is Bad when most words are complex', function (): void {
    $html = '<p>'.str_repeat('Extraordinarily sophisticated international communication responsibility organizations. ', 5).'</p>';

    expect((new WordComplexityCheck)->run(new ContentContext(contentHtml: $html))->status)->toBe(Status::Bad);
});
