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

/**
 * Regression: occurrences were counted with substr_count(' kw ') on the text, which consumes
 * the separating space, so back-to-back repeats were undercounted (four in a row counted as
 * two) and a phrase touching punctuation ("espresso,") was missed, skewing the score.
 */
function densityMessage(string $html, string $keyphrase): string
{
    return (new KeywordDensityCheck)->run(new ContentContext(contentHtml: $html, focusKeyphrase: $keyphrase))->message;
}

it('counts back-to-back repeats of the keyphrase', function (): void {
    // 100 words, 4 of them "espresso" in a row: 4%, not the 2% the old count reported.
    $html = '<p>'.str_repeat('filler ', 96).'espresso espresso espresso espresso</p>';

    expect(densityMessage($html, 'espresso'))->toContain('(4%)');
});

it('counts a keyphrase that touches punctuation', function (): void {
    $html = '<p>'.str_repeat('filler ', 97).'espresso, espresso. (espresso)</p>';

    expect(densityMessage($html, 'espresso'))->toContain('(3%)');
});

it('is case-insensitive and tolerates any whitespace between the keyphrase words', function (): void {
    $html = '<p>'.str_repeat('filler ', 98)."Coffee   Roasting\ncoffee roasting</p>";

    // 102 words with two occurrences of the two-word phrase.
    expect(densityMessage($html, 'coffee roasting'))->toContain('(1.96%)');
});

it('does not count the keyphrase inside a longer word', function (): void {
    $html = '<p>'.str_repeat('filler ', 97).'espressos espresso-machine nonespresso</p>';

    // "espresso-machine" is the only whole-word hit; "espressos" and "nonespresso" are not.
    expect(densityMessage($html, 'espresso'))->toContain('(1%)');
});

it('matches non-Latin keyphrases whole-word as well', function (): void {
    $html = '<p>'.str_repeat('كلمة ', 97).'قهوة قهوة قهوة</p>';

    expect(densityMessage($html, 'قهوة'))->toContain('(3%)');
});

/**
 * How many times the check counted the keyphrase, read back from its public result:
 * density (%) x word count / 100. Exercises the real run() path, not a helper.
 */
function countedKeyphrase(string $text, string $keyphrase): int
{
    $context = new ContentContext(contentHtml: '<p>'.$text.'</p>', focusKeyphrase: $keyphrase);
    $result = (new KeywordDensityCheck)->run($context);

    if (str_contains($result->message, 'does not appear')) {
        return 0;
    }

    preg_match('/\(([\d.]+)%\)/', $result->message, $match);

    return (int) round(((float) $match[1]) * $context->extracted()->wordCount / 100);
}

it('counts multi-word adjacent repeats and keyphrases at the edges of the text', function (): void {
    expect(countedKeyphrase('coffee roasting coffee roasting', 'coffee roasting'))->toBe(2)
        ->and(countedKeyphrase('Coffee. Good coffee, strong coffee! (coffee)', 'coffee'))->toBe(4);
});

it('does not count the keyphrase glued to digits or as a word fragment', function (): void {
    expect(countedKeyphrase('coffees and decoffee and coffee2 and coffee', 'coffee'))->toBe(1);
});

it('matches accented Latin keyphrases', function (): void {
    expect(countedKeyphrase('Un café, deux café.', 'café'))->toBe(2);
});

it('treats regex characters in the keyphrase literally', function (): void {
    expect(countedKeyphrase('learn c++ today, c++ rocks', 'c++'))->toBe(2);
});

it('scores adjacent repeats with the corrected count', function (): void {
    // 3 occurrences in 100 words is 3%: above the 2.5% ideal. The old substr_count saw only 2 (2%, ideal).
    $result = (new KeywordDensityCheck)->run(new ContentContext(
        contentHtml: '<p>coffee coffee coffee '.str_repeat('word ', 97).'</p>',
        focusKeyphrase: 'coffee',
    ));

    expect($result->status)->toBe(Status::Ok)
        ->and($result->message)->toContain('(3%)');
});
