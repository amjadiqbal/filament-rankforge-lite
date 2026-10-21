<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine;

final class ContentContext
{
    private ?ExtractedContent $extracted = null;

    /**
     * @param  string[]  $relatedKeyphrases
     */
    public function __construct(
        public readonly string $title = '',
        public readonly string $description = '',
        public readonly string $slug = '',
        public readonly string $canonicalUrl = '',
        public readonly string $contentHtml = '',
        public readonly string $focusKeyphrase = '',
        public readonly array $relatedKeyphrases = [],
        public readonly string $language = 'en',
        public readonly bool $cornerstone = false,
    ) {}

    public function extracted(): ExtractedContent
    {
        return $this->extracted ??= HtmlTextExtractor::extract($this->contentHtml);
    }

    public function withFocusKeyphrase(string $focusKeyphrase): self
    {
        return new self(
            title: $this->title,
            description: $this->description,
            slug: $this->slug,
            canonicalUrl: $this->canonicalUrl,
            contentHtml: $this->contentHtml,
            focusKeyphrase: $focusKeyphrase,
            relatedKeyphrases: $this->relatedKeyphrases,
            language: $this->language,
            cornerstone: $this->cornerstone,
        );
    }

    /**
     * @param  array{
     *     title?: string,
     *     description?: string,
     *     slug?: string,
     *     canonicalUrl?: string,
     *     contentHtml?: string,
     *     focusKeyphrase?: string,
     *     relatedKeyphrases?: string[],
     *     language?: string,
     *     cornerstone?: bool,
     * }  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? '',
            description: $data['description'] ?? '',
            slug: $data['slug'] ?? '',
            canonicalUrl: $data['canonicalUrl'] ?? '',
            contentHtml: $data['contentHtml'] ?? '',
            focusKeyphrase: $data['focusKeyphrase'] ?? '',
            relatedKeyphrases: $data['relatedKeyphrases'] ?? [],
            language: $data['language'] ?? 'en',
            cornerstone: $data['cornerstone'] ?? false,
        );
    }
}
