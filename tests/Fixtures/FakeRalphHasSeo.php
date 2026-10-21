<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Tests\Fixtures;

/**
 * The same method shape as ralphjsmit/laravel-seo's HasSEO trait, without
 * pulling that package in: using it next to HasSeo must not collide.
 */
trait FakeRalphHasSeo
{
    protected static function bootHasSEO(): void {}
}
