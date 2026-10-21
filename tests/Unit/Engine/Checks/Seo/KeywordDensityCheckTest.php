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

it('counts adjacent repeats of the keyphrase separately', function (): void {
    expect(KeywordDensityCheck::countOccurrences('coffee coffee coffee', 'coffee'))->toBe(3)
        ->and(KeywordDensityCheck::countOccurrences('coffee roasting coffee roasting', 'coffee roasting'))->toBe(2);
});

it('counts the keyphrase before punctuation and at the edges of the text', function (): void {
    expect(KeywordDensityCheck::countOccurrences('Coffee. Good coffee, strong coffee! (coffee)', 'coffee'))->toBe(4);
});

it('only counts whole words', function (): void {
    expect(KeywordDensityCheck::countOccurrences('coffees and decoffee and coffee2 and coffee', 'coffee'))->toBe(1);
});

it('matches multi-word keyphrases across any whitespace, case-insensitively', function (): void {
    expect(KeywordDensityCheck::countOccurrences("Best  Coffee\nBeans here", '  best coffee   beans '))->toBe(1);
});

it('matches non-Latin keyphrases', function (): void {
    expect(KeywordDensityCheck::countOccurrences('قهوة جيدة. قهوة قهوة', 'قهوة'))->toBe(3)
        ->and(KeywordDensityCheck::countOccurrences('Un café, deux café.', 'café'))->toBe(2);
});

it('treats regex characters in the keyphrase literally', function (): void {
    expect(KeywordDensityCheck::countOccurrences('learn c++ today, c++ rocks', 'c++'))->toBe(2)
        ->and(KeywordDensityCheck::countOccurrences('anything', ''))->toBe(0);
});

it('scores repeated keyphrases with the corrected count', function (): void {
    // 3 occurrences in 100 words is 3%: above the 2.5% ideal. The old substr_count saw only 2 (2%, ideal).
    $context = new ContentContext(
        contentHtml: '<p>coffee coffee coffee '.str_repeat('word ', 97).'</p>',
        focusKeyphrase: 'coffee',
    );

    $result = (new KeywordDensityCheck)->run($context);

    expect($result->status)->toBe(Status::Ok)
        ->and($result->message)->toContain('(3%)');
});
