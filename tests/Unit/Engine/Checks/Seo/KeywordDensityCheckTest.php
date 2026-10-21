<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Seo\KeywordDensityCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is Bad when the keyphrase never appears', function (): void {
    $context = new ContentContext(
        contentHtml: '<p>'.str_repeat('word ', 100).'</p>',
        focusKeyphrase: 'coffee roasting',
    );

    $result = (new KeywordDensityCheck)->run($context);

    expect($result->status)->toBe(Status::Bad)
        ->and($result->message)->toContain('does not appear');
});

it('is Bad when the keyphrase is stuffed', function (): void {
    $context = new ContentContext(
        contentHtml: '<p>'.str_repeat('coffee roasting ', 10).str_repeat('word ', 90).'</p>',
        focusKeyphrase: 'coffee roasting',
    );

    $result = (new KeywordDensityCheck)->run($context);

    expect($result->status)->toBe(Status::Bad)
        ->and($result->message)->toContain('stuffing');
});
