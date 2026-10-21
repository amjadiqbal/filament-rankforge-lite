<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine;

final readonly class ExtractedContent
{
    /**
     * @param  array<int, array{level: int, text: string}>  $headings
     * @param  string[]  $paragraphs
     * @param  string[]  $sentences
     * @param  array<int, array{href: string, text: string}>  $links
     * @param  array<int, array{src: string, alt: string}>  $images
     * @param  int[]  $headingWordGaps  word counts between successive structural boundaries (headings)
     */
    public function __construct(
        public string $plainText,
        public int $wordCount,
        public array $headings,
        public string $firstParagraph,
        public array $paragraphs,
        public array $sentences,
        public array $links,
        public array $images,
        public array $headingWordGaps,
    ) {}
}
