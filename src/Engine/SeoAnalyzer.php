<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine;

use AmjadIqbal\FilamentRankForge\Keyphrase\KeyphraseAnalyzer;
use InvalidArgumentException;

final class SeoAnalyzer
{
    public function __construct(private readonly CheckRegistry $registry = new CheckRegistry) {}

    public static function analyzer(): self
    {
        return new self;
    }

    /**
     * @param  array<string, mixed>|ContentContext|object  $context  An array of ContentContext fields, a
     *                                                               ContentContext, or a model using the HasSeo trait.
     */
    public static function analyze(array|object $context): Report
    {
        return self::analyzer()->run($context);
    }

    /**
     * Alias of analyze() for a model using the HasSeo trait — reads more clearly at call sites.
     */
    public static function analyzeModel(object $model): Report
    {
        return self::analyze($model);
    }

    public function withCheck(Check $check): self
    {
        return new self($this->registry->withCheck($check));
    }

    public function withoutCheck(string $id): self
    {
        return new self($this->registry->withoutCheck($id));
    }

    /**
     * @param  array<string, mixed>|ContentContext|object  $context
     */
    public function run(array|object $context): Report
    {
        $context = $this->toContext($context);

        $results = [];
        foreach ($this->registry->all() as $check) {
            if (! $check->isApplicable($context)) {
                continue;
            }
            $results[] = $check->run($context);
        }

        return new Report($results);
    }

    /**
     * @param  array<string, mixed>|ContentContext|object  $context
     * @return array<string, CheckResult[]>
     */
    public function analyzeKeyphrases(array|object $context): array
    {
        return (new KeyphraseAnalyzer($this->registry))->analyzeRelated($this->toContext($context));
    }

    /**
     * @param  array<string, mixed>|ContentContext|object  $context
     */
    private function toContext(array|object $context): ContentContext
    {
        if ($context instanceof ContentContext) {
            return $context;
        }

        if (is_array($context)) {
            return ContentContext::fromArray($context);
        }

        if (method_exists($context, 'toSeoContentContext')) {
            /** @var ContentContext */
            return $context->toSeoContentContext();
        }

        throw new InvalidArgumentException(sprintf(
            '%s must define a toSeoContentContext(): ContentContext method (see the HasSeo trait), or pass a ContentContext/array directly.',
            $context::class,
        ));
    }
}
