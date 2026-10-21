<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Readability\TransitionWordsCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is Bad with zero transition words', function (): void {
    $context = new ContentContext(
        contentHtml: '<p>Cats sleep a lot. Dogs bark at strangers. Fish swim in tanks. Birds fly south.</p>',
    );

    expect((new TransitionWordsCheck)->run($context)->status)->toBe(Status::Bad);
});

it('is Good when most sentences contain a transition word', function (): void {
    $context = new ContentContext(
        contentHtml: '<p>However, cats sleep a lot. Therefore, they need soft beds. In addition, they enjoy sunny spots.</p>',
    );

    expect((new TransitionWordsCheck)->run($context)->status)->toBe(Status::Good);
});
