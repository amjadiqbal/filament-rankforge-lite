<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\HtmlTextExtractor;

it('returns empty extraction for empty html', function (): void {
    $extracted = HtmlTextExtractor::extract('');

    expect($extracted->wordCount)->toBe(0)
        ->and($extracted->headings)->toBe([])
        ->and($extracted->paragraphs)->toBe([])
        ->and($extracted->sentences)->toBe([]);
});

it('extracts headings, paragraphs, links, and images in document order', function (): void {
    $extracted = HtmlTextExtractor::extract(
        '<h2>Intro</h2><p>One two three four five.</p><h3>More</h3><p>Six seven eight.</p>'
        .'<a href="/a">A</a><a href="https://other.example/b">B</a>'
        .'<img src="/x.jpg" alt="An image" /><img src="/y.jpg" alt="" />'
    );

    expect($extracted->wordCount)->toBe(10)
        ->and($extracted->headings)->toHaveCount(2)
        ->and($extracted->headings[0])->toBe(['level' => 2, 'text' => 'Intro'])
        ->and($extracted->paragraphs)->toHaveCount(2)
        ->and($extracted->firstParagraph)->toBe('One two three four five.')
        ->and($extracted->links)->toHaveCount(2)
        ->and($extracted->images)->toHaveCount(2)
        ->and($extracted->images[0]['alt'])->toBe('An image')
        ->and($extracted->headingWordGaps)->toHaveCount(3);
});

it('does not merge adjacent block elements without whitespace between tags', function (): void {
    $extracted = HtmlTextExtractor::extract('<h2>Intro</h2><p>Body text.</p>');

    expect($extracted->plainText)->toBe('Intro Body text.');
});

it('splits sentences on terminal punctuation', function (): void {
    $extracted = HtmlTextExtractor::extract('<p>First sentence. Second sentence! Third sentence?</p>');

    expect($extracted->sentences)->toBe([
        'First sentence.',
        'Second sentence!',
        'Third sentence?',
    ]);
});
