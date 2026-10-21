# Changelog

All notable changes to `amjadiqbal/filament-rankforge-lite` are documented here, following [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

## [1.0.1] - 2026-10-21

### Fixed

- Keyword density counted adjacent repeats of the keyphrase once ("coffee coffee" counted as 1) and missed it before punctuation ("coffee."). It now counts whole-phrase matches.
- Canonical URL and Open Graph image now accept URLs up to 2048 characters: a new migration widens both columns, matching the full RankForge suite.

### Added

- `analysis.disabled_checks`, `analysis.weights_override`, `analysis.keyword_density_range` and `analysis.content_length_tiers` config keys, with the same behaviour as RankForge.

## [1.0.0] - 2026-10-21

### Added

- `HasSeo` trait: one `rankforge_meta` row per record, with the score kept in sync on every save, including saves made outside Filament.
- `SeoFields` form section: focus and related keyphrases, meta title and description, canonical URL, robots, and Open Graph title, description and image.
- Live analysis panel: overall, SEO and readability scores, 22 checks in 6 languages, a desktop/mobile SERP preview and Facebook, X and LinkedIn card previews.
- `SeoScoreColumn` for tables: sortable, coloured red, yellow or green.
- `<x-rankforge::tags>` and `@rankforge`: title, description, robots, canonical, hreflang, Open Graph and X card tags.
- Works next to `ralphjsmit/laravel-seo` on the same model (no trait boot-method collision).
- Same namespace, table, migration names and config keys as RankForge, so upgrading needs no content migration.
