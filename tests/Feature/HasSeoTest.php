<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Engine\Report;
use AmjadIqbal\FilamentRankForge\Engine\SeoAnalyzer;
use AmjadIqbal\FilamentRankForge\Models\RankForgeMeta;
use AmjadIqbal\FilamentRankForge\Tests\Fixtures\FakeRalphHasSeo;
use AmjadIqbal\FilamentRankForge\Tests\Fixtures\Post;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function (): void {
    Schema::create('posts', function (Blueprint $table): void {
        $table->id();
        $table->string('title')->nullable();
        $table->string('slug')->nullable();
        $table->text('content')->nullable();
        $table->timestamps();
    });
});

function makeGoodPost(): Post
{
    return Post::create([
        'title' => 'Best Coffee Beans for Espresso — The Complete Buying Guide',
        'slug' => 'best-coffee-beans-for-espresso',
        'content' => '<p>Espresso brewing starts with choosing the best coffee beans for espresso.</p>'
            .'<h2>Why the best coffee beans for espresso matter</h2>'
            .'<p>'.str_repeat('word ', 300).'</p>',
    ]);
}

it('creates the rankforgeMeta row when the record is saved', function (): void {
    $post = makeGoodPost();

    expect($post->rankforgeMeta)->not->toBeNull()
        ->and($post->rankforgeMeta->model_id)->toBe($post->id)
        ->and($post->rankforgeMeta->model_type)->toBe(Post::class);
});

it('builds a ContentContext from the model attributes', function (): void {
    $post = makeGoodPost();

    $context = $post->toSeoContentContext();

    expect($context->title)->toBe($post->title)
        ->and($context->slug)->toBe($post->slug)
        ->and($context->contentHtml)->toBe($post->content)
        ->and($context->focusKeyphrase)->toBe('');
});

it('produces a Report via seoReport()', function (): void {
    $post = makeGoodPost();

    expect($post->seoReport())->toBeInstanceOf(Report::class);
});

it('analyzes a model through SeoAnalyzer::analyzeModel()', function (): void {
    $post = makeGoodPost();

    $report = SeoAnalyzer::analyzeModel($post);

    expect($report)->toBeInstanceOf(Report::class)
        ->and($report->results)->not->toBeEmpty();
});

it('persists the score to rankforge_meta on save', function (): void {
    $post = makeGoodPost();

    $post->refresh();
    $meta = $post->rankforgeMeta;

    expect($meta)->not->toBeNull()
        ->and($meta->score)->not->toBeNull()
        ->and($meta->seo_score)->not->toBeNull()
        ->and($meta->readability_score)->not->toBeNull();
});

it('updates the persisted score on subsequent saves instead of duplicating rows', function (): void {
    $post = makeGoodPost();
    $post->update(['title' => 'A different title entirely for this post']);

    expect(RankForgeMeta::query()->where('model_id', $post->id)->count())->toBe(1);
});

it('re-scores the record when its rankforge_meta row is saved, as Filament does after the record', function (): void {
    $post = makeGoodPost();
    $before = $post->rankforgeMeta->fresh()->score;

    $meta = RankForgeMeta::query()->where('model_id', $post->id)->firstOrFail();
    $meta->update([
        'focus_keyphrase' => 'best coffee beans for espresso',
        'meta_title' => 'Best Coffee Beans for Espresso: A Buying Guide',
        'meta_description' => 'Find the best coffee beans for espresso with our guide to roast level, origin and freshness, plus the five bags we keep coming back to.',
    ]);

    expect($meta->fresh()->score)->not->toBe($before);
});

it('does not boot through a trait method, so it can share a model with ralphjsmit/laravel-seo', function (): void {
    // ralphjsmit/laravel-seo's HasSEO declares bootHasSEO(); PHP method names
    // are case-insensitive, so a bootHasSeo() here would be a fatal collision.
    expect(method_exists(Post::class, 'bootHasSeo'))->toBeFalse();

    $model = new class extends Post
    {
        use FakeRalphHasSeo;
    };

    expect($model)->toBeInstanceOf(Post::class);
});

it('skips scoring for an unsaved record', function (): void {
    $post = new Post(['title' => 'Draft']);
    $post->syncSeoScore();

    expect(RankForgeMeta::query()->count())->toBe(0);
});
