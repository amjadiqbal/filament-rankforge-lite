<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;
use Illuminate\View\Component;

/**
 * Backs the x-rankforge::tags component and the rankforge Blade directive.
 * Renders the SEO head tags: title, meta description, robots, canonical,
 * hreflang, Open Graph and X (Twitter) cards.
 *
 * Model mode:  x-rankforge::tags :model="$post"
 * Static mode: x-rankforge::tags title="Contact" description="..."
 *
 * Values are resolved with this precedence: explicit component prop >
 * rankforge_meta override (via the model's HasSeo trait) >
 * ContentContext fallback (via the model's toSeoContentContext()) >
 * site-wide config('rankforge.frontend.*') default.
 */
class Tags extends Component
{
    /**
     * @param  array<string, string>  $robots
     * @param  array<string, string>  $hreflang  locale => URL
     */
    public function __construct(
        public mixed $model = null,
        public ?string $title = null,
        public ?string $description = null,
        public ?string $canonicalUrl = null,
        public array $robots = [],
        public ?string $ogTitle = null,
        public ?string $ogDescription = null,
        public ?string $ogImage = null,
        public array $hreflang = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function meta(): array
    {
        $model = is_object($this->model) ? $this->model : null;

        $meta = ($model !== null && method_exists($model, 'rankforgeMeta'))
            ? $model->rankforgeMeta
            : null;

        $context = ($model !== null && method_exists($model, 'toSeoContentContext'))
            ? $model->toSeoContentContext()
            : null;

        $title = $this->pick($this->title, $meta?->translatedMetaTitle(), $context?->title, config('rankforge.frontend.default_title'));
        $description = $this->pick($this->description, $meta?->translatedMetaDescription(), $context?->description, config('rankforge.frontend.default_description'));
        $canonical = $this->pick($this->canonicalUrl, $meta?->canonical_url, $context?->canonicalUrl) ?? url()->current();
        $robots = $this->robots !== [] ? $this->robots : ($meta?->robots ?? config('rankforge.frontend.default_robots', ['index', 'follow']));
        $ogTitle = $this->pick($this->ogTitle, $meta?->og_title, $title);
        $ogDescription = $this->pick($this->ogDescription, $meta?->og_description, $description);
        $ogImage = $this->pick($this->ogImage, $meta?->og_image, config('rankforge.frontend.default_og_image'));

        // There's no universal per-locale URL scheme a package can rely on,
        // so hreflang is duck-typed on an optional
        // getSeoLocaleUrls(): array<string, string> method on the model. The
        // explicit hreflang prop always wins when given.
        $hreflang = $this->hreflang;
        if ($hreflang === [] && $model !== null && method_exists($model, 'getSeoLocaleUrls')) {
            $hreflang = $model->getSeoLocaleUrls();
        }

        return [
            'title' => $title,
            'description' => $description,
            'canonical' => $canonical,
            'robots' => $robots,
            'ogTitle' => $ogTitle,
            'ogDescription' => $ogDescription,
            'ogImage' => $ogImage,
            'siteName' => config('rankforge.frontend.site_name', config('app.name')),
            'twitterSite' => config('rankforge.frontend.twitter_site'),
            'hreflang' => $hreflang,
        ];
    }

    public function render(): View
    {
        return view('rankforge::components.tags', ['meta' => $this->meta()]);
    }

    /**
     * Entry point for the rankforge Blade directive, called as
     * rankforge($post) or rankforge(['title' => 'Contact', 'description' => '...']).
     *
     * @param  array<string, mixed>  $overrides
     */
    public static function renderFor(mixed $modelOrProps = null, array $overrides = []): HtmlString
    {
        $props = is_array($modelOrProps)
            ? array_merge($modelOrProps, $overrides)
            : array_merge(['model' => $modelOrProps], $overrides);

        $component = new self(...$props);
        $view = $component->render();

        return new HtmlString($view->render());
    }

    private function pick(?string ...$values): ?string
    {
        foreach ($values as $value) {
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }
}
