<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Readability\PassiveVoiceCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is Good with mostly active voice', function (): void {
    $context = new ContentContext(contentHtml: '<p>She wrote the report. They baked the cake. He owns the house.</p>');

    expect((new PassiveVoiceCheck)->run($context)->status)->toBe(Status::Good);
});

it('is Bad when most sentences are passive', function (): void {
    $context = new ContentContext(
        contentHtml: '<p>The report was written by the team. The cake was baked by her. The house is owned by them. They walked to the store.</p>',
    );

    expect((new PassiveVoiceCheck)->run($context)->status)->toBe(Status::Bad);
});

it('reports 0% for languages with no auxiliary indicator list', function (): void {
    $context = new ContentContext(contentHtml: '<p>مرحبا بكم في هذا المقال</p>', language: 'ar');

    expect((new PassiveVoiceCheck)->run($context)->status)->toBe(Status::Good);
});
