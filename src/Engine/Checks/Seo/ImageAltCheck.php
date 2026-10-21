<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Checks\Seo;

use AmjadIqbal\FilamentRankForge\Engine\Check;
use AmjadIqbal\FilamentRankForge\Engine\CheckResult;
use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Status;

final class ImageAltCheck implements Check
{
    public function __construct(private readonly int $weight = 6) {}

    public function id(): string
    {
        return 'image-alt';
    }

    public function group(): string
    {
        return 'seo';
    }

    public function weight(): int
    {
        return $this->weight;
    }

    public function isApplicable(ContentContext $context): bool
    {
        return $context->contentHtml !== '';
    }

    public function run(ContentContext $context): CheckResult
    {
        $images = $context->extracted()->images;

        if ($images === []) {
            return new CheckResult(
                $this->id(),
                $this->group(),
                Status::Ok,
                'No images were found in the content.',
                $this->weight,
            );
        }

        $withAlt = array_filter($images, fn (array $image): bool => $image['alt'] !== '');
        // Cast to float: PHP's `/` returns an int when evenly divisible, which
        // would silently break the strict `=== 1.0` comparison below.
        $coverage = (float) count($withAlt) / count($images);

        $keyphraseFound = $context->focusKeyphrase === '' || array_reduce(
            $withAlt,
            fn (bool $carry, array $image): bool => $carry || mb_stripos($image['alt'], $context->focusKeyphrase) !== false,
            false,
        );

        $status = match (true) {
            $coverage === 1.0 && $keyphraseFound => Status::Good,
            $coverage > 0.0 => Status::Ok,
            default => Status::Bad,
        };

        $percentage = (int) round($coverage * 100);

        $message = match ($status) {
            Status::Good => 'All images have descriptive alt text.',
            Status::Ok => "{$percentage}% of images have alt text — add alt text to the rest and include the focus keyphrase where relevant.",
            Status::Bad => 'None of the images have alt text.',
        };

        return new CheckResult($this->id(), $this->group(), $status, $message, $this->weight);
    }
}
