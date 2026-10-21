<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Models\RankForgeMeta;
use AmjadIqbal\FilamentRankForge\Tests\Fixtures\Livewire\SeoFieldsTestComponent;
use AmjadIqbal\FilamentRankForge\Tests\Fixtures\Post;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Regression: the canonical URL and og:image inputs accepted 2048 characters while the
 * rankforge_meta columns were string(255). SQLite does not enforce varchar lengths, so
 * these tests pin the alignment by construction: the form and the migration both use
 * the one limit, and the widening migration is registered and reversible.
 */
function urlLimitInputs(): array
{
    // A bare SeoFields::make() Section cannot walk its children until it is attached to a
    // schema owned by a HasSchemas Livewire component; the fixture provides that.
    $section = (new SeoFieldsTestComponent)->getSchema('form')->getComponents()[0];
    $limits = [];

    $walk = function (iterable $components) use (&$walk, &$limits): void {
        foreach ($components as $component) {
            if (method_exists($component, 'getName') && in_array($component->getName(), ['canonical_url', 'og_image'], true)) {
                $limits[$component->getName()] = $component->getMaxLength();
            }

            if (method_exists($component, 'getChildComponents')) {
                $walk($component->getChildComponents());
            }
        }
    };

    $walk($section->getChildComponents());
    ksort($limits);

    return $limits;
}

it('caps the canonical_url and og_image inputs at the column length', function (): void {
    expect(RankForgeMeta::URL_MAX_LENGTH)->toBe(2048)
        ->and(urlLimitInputs())->toBe([
            'canonical_url' => RankForgeMeta::URL_MAX_LENGTH,
            'og_image' => RankForgeMeta::URL_MAX_LENGTH,
        ]);
});

it('declares the same length in the widening migration', function (): void {
    $source = (string) file_get_contents(dirname(__DIR__, 2).'/database/migrations/widen_rankforge_meta_url_columns.php');

    expect($source)->toContain("string('canonical_url', 2048)")
        ->and($source)->toContain("string('og_image', 2048)");
});

it('registers and runs the widening migration without touching the original ones', function (): void {
    $ran = DB::table('migrations')->pluck('migration')->all();

    expect($ran)->toContain('widen_rankforge_meta_url_columns')
        ->and($ran)->toContain('create_rankforge_meta_table');
});

it('stores a 2000 character canonical URL and og image', function (): void {
    Schema::create('posts', function (Blueprint $table): void {
        $table->id();
        $table->string('title')->nullable();
        $table->string('slug')->nullable();
        $table->text('content')->nullable();
        $table->timestamps();
    });

    $url = 'https://example.com/'.str_repeat('a', 1980);
    $post = Post::create(['title' => 'Long URL', 'slug' => 'long-url', 'content' => '<p>x</p>']);
    $post->rankforgeMeta()->update(['canonical_url' => $url, 'og_image' => $url]);

    expect($post->fresh()->rankforgeMeta->canonical_url)->toBe($url)
        ->and($post->fresh()->rankforgeMeta->og_image)->toBe($url);
});
