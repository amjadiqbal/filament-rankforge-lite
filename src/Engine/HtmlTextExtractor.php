<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine;

use DOMDocument;
use DOMElement;
use DOMXPath;

final class HtmlTextExtractor
{
    public static function extract(string $html): ExtractedContent
    {
        if (trim($html) === '') {
            return new ExtractedContent(
                plainText: '',
                wordCount: 0,
                headings: [],
                firstParagraph: '',
                paragraphs: [],
                sentences: [],
                links: [],
                images: [],
                headingWordGaps: [],
            );
        }

        $document = new DOMDocument;
        libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="utf-8" ?><div>'.$html.'</div>',
            LIBXML_NOERROR | LIBXML_NOWARNING
        );
        libxml_clear_errors();

        $xpath = new DOMXPath($document);

        $headings = [];
        $paragraphs = [];
        $headingWordGaps = [];
        $textParts = [];
        $currentGap = 0;

        $structuralNodes = $xpath->query('//p | //h1 | //h2 | //h3 | //h4 | //h5 | //h6');

        if ($structuralNodes !== false) {
            foreach ($structuralNodes as $node) {
                if (! $node instanceof DOMElement) {
                    continue;
                }

                $tag = strtolower($node->nodeName);
                $text = self::normalizeWhitespace($node->textContent);

                if ($text !== '') {
                    $textParts[] = $text;
                }

                if ($tag === 'p') {
                    if ($text !== '') {
                        $paragraphs[] = $text;
                    }
                    $currentGap += self::countWords($text);

                    continue;
                }

                $headings[] = [
                    'level' => (int) substr($tag, 1, 1),
                    'text' => $text,
                ];
                $headingWordGaps[] = $currentGap;
                $currentGap = 0;
            }
        }

        $headingWordGaps[] = $currentGap;

        // Block elements (p/h1-h6) carry the readable text. Only fall back
        // to the raw document text when none were found at all — e.g. bare
        // text nodes with no paragraph/heading markup — since textContent()
        // concatenates adjacent elements without inserting whitespace.
        $plainText = $textParts !== []
            ? implode(' ', $textParts)
            : self::normalizeWhitespace((string) $document->documentElement?->textContent);
        $wordCount = self::countWords($plainText);

        $firstParagraph = $paragraphs[0] ?? $plainText;

        $links = [];
        $linkNodes = $xpath->query('//a');
        if ($linkNodes !== false) {
            foreach ($linkNodes as $node) {
                if (! $node instanceof DOMElement) {
                    continue;
                }
                $links[] = [
                    'href' => trim($node->getAttribute('href')),
                    'text' => self::normalizeWhitespace($node->textContent),
                ];
            }
        }

        $images = [];
        $imageNodes = $xpath->query('//img');
        if ($imageNodes !== false) {
            foreach ($imageNodes as $node) {
                if (! $node instanceof DOMElement) {
                    continue;
                }
                $images[] = [
                    'src' => trim($node->getAttribute('src')),
                    'alt' => trim($node->getAttribute('alt')),
                ];
            }
        }

        return new ExtractedContent(
            plainText: $plainText,
            wordCount: $wordCount,
            headings: $headings,
            firstParagraph: $firstParagraph,
            paragraphs: $paragraphs,
            sentences: self::splitSentences($plainText),
            links: $links,
            images: $images,
            headingWordGaps: $headingWordGaps,
        );
    }

    private static function normalizeWhitespace(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', $text) ?? '');
    }

    private static function countWords(string $text): int
    {
        if (trim($text) === '') {
            return 0;
        }

        $parts = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);

        return $parts === false ? 0 : count($parts);
    }

    /**
     * @return string[]
     */
    private static function splitSentences(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        $parts = preg_split('/(?<=[.!?])\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);

        if ($parts === false) {
            return [];
        }

        return array_values(array_filter(array_map('trim', $parts), fn (string $sentence): bool => $sentence !== ''));
    }
}
