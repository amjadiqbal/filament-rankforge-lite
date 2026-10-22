<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="https://raw.githubusercontent.com/amjadiqbal/filament-rankforge-lite/main/art/banner-dark.png">
    <img alt="RankForge Lite: free SEO fields, live SEO and readability score, and SERP preview for Filament" src="https://raw.githubusercontent.com/amjadiqbal/filament-rankforge-lite/main/art/banner-light.png" width="100%">
  </picture>
</p>

# RankForge Lite

Free SEO for Filament 4 and 5. It adds meta, Open Graph and X (Twitter) fields to any resource, shows a live SEO and readability score as you type, and previews the Google result on desktop and mobile. It's the free edition of [RankForge](https://rankforge.amjad.com.pk/?utm_source=github&utm_medium=readme&utm_campaign=filament-rankforge-lite) and stores its data the same way, so you can upgrade later without migrating any content.

[![Latest Version](https://img.shields.io/packagist/v/amjadiqbal/filament-rankforge-lite.svg?style=flat-square)](https://packagist.org/packages/amjadiqbal/filament-rankforge-lite)
[![PHP Version](https://img.shields.io/packagist/php-v/amjadiqbal/filament-rankforge-lite?style=flat-square)](https://packagist.org/packages/amjadiqbal/filament-rankforge-lite)
[![Filament](https://img.shields.io/badge/Filament-4.x%20%7C%205.x-6D4AFF?style=flat-square)](https://filamentphp.com/plugins)
[![License](https://img.shields.io/packagist/l/amjadiqbal/filament-rankforge-lite.svg?style=flat-square)](https://github.com/amjadiqbal/filament-rankforge-lite/blob/main/LICENSE.md)

<p align="center">
  <img alt="The RankForge Lite SEO section in a Filament edit form: score gauge, check list, SERP preview and social cards" src="https://raw.githubusercontent.com/amjadiqbal/filament-rankforge-lite/main/art/screenshot.png" width="100%">
</p>

## Features

- **SEO fields** on any Eloquent model: focus keyphrase, meta title, meta description, canonical URL and robots directives. Up to 4 related keyphrases each get their own score in the panel from the six keyword checks; they don't change the overall score.
- **Social sharing**: Open Graph title, description and image, plus X (Twitter) card tags that follow them.
- **Live analysis** while you type: an overall score, an SEO sub-score and a readability sub-score from 22 checks, grouped as failed, improvements and good results. Each check runs only when it applies: the keyphrase checks need a focus keyphrase, and the cornerstone depth check needs the cornerstone toggle.
- **Readability checks** for sentence and paragraph length, passive voice, transition words, Flesch reading ease, subheading spread, consecutive sentence starts and word complexity.
- **Six languages** of analysis: English, Spanish, French, German, Arabic and Urdu. For Arabic and Urdu there's no syllable counting or passive-voice detection yet, so Flesch reading ease and word complexity are skipped and passive voice reads 0%. The field labels and panel headings are translated into all six; the check messages are in English.
- **SERP preview** for desktop and mobile, plus Facebook, X and LinkedIn card previews.
- **Score column** for your tables: sortable, and coloured with your panel's danger, warning and success colours (below 40, 40 to 69, and 70 or more; configurable).
- **Front-end tags** from one Blade component: `<title>`, description, robots, canonical, hreflang, Open Graph and X cards.

## Supported versions

| RankForge Lite | Filament | Laravel | PHP |
|---|---|---|---|
| 1.x | 4.x and 5.x | 12.x and 13.x | 8.3+ |

## Installation

```bash
composer require amjadiqbal/filament-rankforge-lite
php artisan migrate
```

The migration creates one table, `rankforge_meta`. You don't need to register a panel plugin or build any assets.

Optionally, publish the config:

```bash
php artisan vendor:publish --tag=rankforge-config
```

## Usage

### 1. Add the trait to your model

```php
use AmjadIqbal\FilamentRankForge\Concerns\HasSeo;

class Post extends Model
{
    use HasSeo;
}
```

The analysis reads `title` (or `name`), `slug` and `content` (or `body`) by default. If your columns are named differently, override the getters:

```php
protected function getSeoTitle(): string { return (string) $this->headline; }
protected function getSeoContentHtml(): string { return (string) $this->article_html; }
protected function getSeoCanonicalUrl(): string { return route('posts.show', $this); }
```

### 2. Add the SEO section to your resource form

```php
use AmjadIqbal\FilamentRankForge\Filament\Forms\SeoFields;

public static function form(Schema $schema): Schema
{
    return $schema->components([
        TextInput::make('title')->live(debounce: 600),
        TextInput::make('slug')->live(debounce: 600),
        RichEditor::make('content')->live(debounce: 600),

        SeoFields::make(contentField: 'content', slugField: 'slug'),
    ]);
}
```

The section loads and saves the record's `rankforge_meta` row through the `rankforgeMeta` relationship. The panel re-scores as you edit any SEO field. Mark the title, slug and content fields `->live()`, as above, and it follows those too. Without a meta title, the panel uses the form's `title` (or `name`) field. RichEditor content works whether it's stored as HTML or JSON.

### 3. Show the score in your table

```php
use AmjadIqbal\FilamentRankForge\Filament\Tables\SeoScoreColumn;

->columns([
    TextColumn::make('title'),
    SeoScoreColumn::make(),
])
->modifyQueryUsing(fn ($query) => $query->with('rankforgeMeta'))
```

Scores are stored each time the record or its SEO fields are saved, including saves made outside Filament.

### 4. Render the tags on your site

In your layout's `<head>`:

```blade
<x-rankforge::tags :model="$post" />

{{-- or the directive --}}
@rankforge($post)

{{-- pages without a model --}}
<x-rankforge::tags title="Contact us" description="Get in touch with our team." />
```

Each value falls back in this order: an explicit prop, the record's SEO fields, the model's own title and description, and finally the site-wide defaults in `config/rankforge.php`. Set `frontend.twitter_site` to your X handle to add `twitter:site`.

## Using it with ralphjsmit/laravel-seo

RankForge Lite can share a model with [ralphjsmit/laravel-seo](https://github.com/ralphjsmit/laravel-seo). Its `HasSeo` trait has no boot method, so it doesn't collide with that package's `HasSEO::bootHasSEO()`. PHP method names are case-insensitive, so a boot method would collide.

The two packages store data separately: Lite uses `rankforge_meta`, laravel-seo uses its `seo` table. If you already render tags with laravel-seo, feed it Lite's fields and keep using `{!! seo()->for($post) !!}`:

```php
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use AmjadIqbal\FilamentRankForge\Concerns\HasSeo;

class Post extends Model
{
    use HasSEO, HasSeo;

    public function getDynamicSEOData(): SEOData
    {
        $meta = $this->rankforgeMeta;

        return new SEOData(
            title: $meta?->meta_title ?: $this->title,
            description: $meta?->meta_description,
            image: $meta?->og_image,
            robots: $meta?->robots ? implode(', ', $meta->robots) : null,
            canonical_url: $meta?->canonical_url,
            openGraphTitle: $meta?->og_title,
        );
    }
}
```

## Configuration

```php
return [
    'analysis' => [
        'default_language' => 'en',            // en, es, fr, de, ar or ur
        'disabled_checks' => [],               // e.g. ['passive-voice']
        'weights_override' => [],              // e.g. ['title-length' => 5]
        'keyword_density_range' => [0.5, 2.5], // ideal keyphrase density, in percent
        'content_length_tiers' => [300, 600, 900], // acceptable and good word counts
    ],
    'frontend' => [
        'site_name' => env('APP_NAME'),
        'default_title' => null,
        'default_description' => null,
        'default_og_image' => null,
        'default_robots' => ['index', 'follow'],
        'twitter_site' => null,          // e.g. '@acme'
    ],
    'score_thresholds' => [
        'red_below' => 40,
        'green_at_or_above' => 70,
    ],
    'lite' => [
        'show_upgrade_link' => true,     // the one-line RankForge link under the analysis panel
    ],
];
```

## Upgrading to RankForge

[RankForge](https://rankforge.amjad.com.pk/?utm_source=github&utm_medium=readme&utm_campaign=filament-rankforge-lite) adds a redirect manager and 404 monitor, an XML sitemap and robots.txt editor, a broken-link scanner, IndexNow and Google Indexing, Gemini AI autofill, generated OG images, JSON-LD schema and an SEO health dashboard.

Lite uses the same PHP namespace, the same `rankforge_meta` table, the same migration names and the same config keys, so upgrading is a package swap:

```bash
composer remove amjadiqbal/filament-rankforge-lite
composer require amjadiqbal/filament-rankforge
php artisan migrate
```

Your models, resources and Blade tags keep working unchanged, and every record keeps its SEO data. The migrate step only adds the full suite's extra tables.

## Testing

```bash
composer test         # Pest
composer analyse      # Larastan (level 6)
composer format       # Laravel Pint
scripts/ci-local.sh   # all of the above on PHP 8.3 and 8.4, Laravel 12 and 13, Filament 4 and 5
```

## Changelog

See [CHANGELOG.md](https://github.com/amjadiqbal/filament-rankforge-lite/blob/main/CHANGELOG.md).

## Security Vulnerabilities

Please report security issues privately through [GitHub's private vulnerability reporting](https://github.com/amjadiqbal/filament-rankforge-lite/security/advisories/new).

## Support

- 🐛 **Bug or feature request**: [open an issue](https://github.com/amjadiqbal/filament-rankforge-lite/issues)
- 💬 **Questions and community**: [Discord](https://discord.com/users/412244229520359434)
- 🔒 **Security issue**: please report privately via GitHub's security tab

### Custom Development

[Hire me on Upwork](https://www.upwork.com/freelancers/amjadkhatri?utm_source=github&utm_medium=readme&utm_campaign=filament-rankforge-lite) for:
- Package integration
- Custom feature development
- Technical consultation
- Project implementation

For priority support and enterprise solutions, please reach out via [Upwork](https://www.upwork.com/freelancers/amjadkhatri?utm_source=github&utm_medium=readme&utm_campaign=filament-rankforge-lite) for direct assistance.

## More packages

Part of a family of open-source packages. See [all of them](https://github.com/amjadiqbal?tab=repositories).

| | |
|---|---|
| [filament-url-image-uploader](https://github.com/amjadiqbal/filament-url-image-uploader) | Upload an image or fetch it from a URL in a Filament form |
| [laravel-tiptap](https://github.com/amjadiqbal/laravel-tiptap) | Tiptap editor for Laravel |
| [laravel-logpulse](https://github.com/amjadiqbal/laravel-logpulse) | Log health monitoring and alerting |

## License

MIT. See [LICENSE.md](https://github.com/amjadiqbal/filament-rankforge-lite/blob/main/LICENSE.md).
