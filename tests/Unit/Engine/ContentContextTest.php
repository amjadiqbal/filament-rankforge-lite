<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\ContentContext;

it('maps array data via fromArray with sensible defaults', function (): void {
    $context = ContentContext::fromArray([
        'title' => 'Hello World',
        'focusKeyphrase' => 'hello',
    ]);

    expect($context->title)->toBe('Hello World')
        ->and($context->focusKeyphrase)->toBe('hello')
        ->and($context->language)->toBe('en')
        ->and($context->cornerstone)->toBeFalse();
});

it('returns a new immutable instance from withFocusKeyphrase', function (): void {
    $context = ContentContext::fromArray(['title' => 'Hello World', 'focusKeyphrase' => 'hello']);
    $variant = $context->withFocusKeyphrase('world');

    expect($variant->focusKeyphrase)->toBe('world')
        ->and($variant->title)->toBe('Hello World')
        ->and($context->focusKeyphrase)->toBe('hello');
});

it('caches the extracted content across calls', function (): void {
    $context = new ContentContext(contentHtml: '<p>Some text.</p>');

    expect($context->extracted())->toBe($context->extracted());
});
