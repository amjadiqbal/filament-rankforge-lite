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
    | default_language is the language pack used when a record has none set:
    | en, es, fr, de, ar or ur.
    |
    */
    'analysis' => [
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
