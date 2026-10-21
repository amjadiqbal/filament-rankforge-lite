<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Checks\Readability\ConsecutiveSentenceStartsCheck;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

it('is Good with no repetitive sentence starts', function (): void {
    $context = new ContentContext(contentHtml: '<p>Cats sleep a lot. Dogs bark often. Birds fly south for winter.</p>');

    expect((new ConsecutiveSentenceStartsCheck)->run($context)->status)->toBe(Status::Good);
});

it('is Bad when 3+ consecutive sentences share the same first word', function (): void {
    $context = new ContentContext(
        contentHtml: '<p>The cat sat on the mat. The cat looked around the room. The cat then fell asleep quickly. Dogs are different animals entirely.</p>',
    );

    expect((new ConsecutiveSentenceStartsCheck)->run($context)->status)->toBe(Status::Bad);
});
