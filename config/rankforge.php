<?php

declare(strict_types=1);

/*
 * RankForge Lite reads a subset of the full RankForge config, under the same
 * keys, so a published config/rankforge.php keeps working after an upgrade.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Analysis Engine
    |--------------------------------------------------------------------------
    |
    | Read by AmjadIqbal\FilamentRankForge\Engine\CheckRegistry, with the same
    | keys and behaviour as the full RankForge suite.
    |
    |  default_language:      en, es, fr, de, ar or ur, used when a record has none.
    |  disabled_checks:       check ids to leave out, e.g. ['passive-voice'].
    |  weights_override:      check id => weight (0 or more), e.g. ['title-length' => 5].
    |  keyword_density_range: [min, max] percent that counts as ideal density.
    |  content_length_tiers:  word counts; the first two are the acceptable and
    |                         good thresholds (the third is reserved).
    |
    | Invalid values fall back to the defaults shown here.
    |
    */
    'analysis' => [
        'disabled_checks' => [],
        'weights_override' => [],
        'keyword_density_range' => [0.5, 2.5],
        'content_length_tiers' => [300, 600, 900],
        'default_language' => 'en',
    ],

    /*
    |--------------------------------------------------------------------------
    | Frontend Rendering
    |--------------------------------------------------------------------------
    |
    | Site-wide fallbacks used by <x-rankforge::tags /> and @rankforge when a
    | model or prop doesn't provide its own value. twitter_site is your X
    | handle (e.g. '@acme'); the other twitter:* tags follow the Open Graph
    | values.
    |
    */
    'frontend' => [
        'site_name' => env('APP_NAME'),
        'default_title' => null,
        'default_description' => null,
        'default_og_image' => null,
        'default_robots' => ['index', 'follow'],
        'twitter_site' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Score Thresholds
    |--------------------------------------------------------------------------
    |
    | Colours for the score gauge and SeoScoreColumn: below red_below is red,
    | at or above green_at_or_above is green, anything between is yellow.
    |
    */
    'score_thresholds' => [
        'red_below' => 40,
        'green_at_or_above' => 70,
    ],

    /*
    |--------------------------------------------------------------------------
    | Upgrade hint
    |--------------------------------------------------------------------------
    |
    | Shows a one-line "More SEO tools in RankForge" link at the bottom of the
    | analysis panel. Set to false to hide it.
    |
    */
    'lite' => [
        'show_upgrade_link' => true,
    ],

];
