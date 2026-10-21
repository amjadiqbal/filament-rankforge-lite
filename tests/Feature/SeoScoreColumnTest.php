<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Filament\Tables\SeoScoreColumn;
use AmjadIqbal\FilamentRankForge\Models\RankForgeMeta;
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

it('is gray with no score, red below 40, yellow 40-69, and green at 70+', function (): void {
    $column = SeoScoreColumn::make();

    $cases = [
        [null, 'gray'],
        [0, 'danger'],
        [39, 'danger'],
        [40, 'warning'],
        [69, 'warning'],
        [70, 'success'],
        [100, 'success'],
    ];

    foreach ($cases as [$score, $expectedColor]) {
        expect($column->getColor($score))->toBe($expectedColor);
    }
});

it('sorts using a subquery against rankforge_meta', function (): void {
    $high = Post::create(['title' => 'High']);
    RankForgeMeta::query()->where('model_id', $high->id)->update(['score' => 90]);

    $low = Post::create(['title' => 'Low']);
    RankForgeMeta::query()->where('model_id', $low->id)->update(['score' => 10]);

    $column = SeoScoreColumn::make();
    $query = Post::query();

    $sorted = $column->applySort($query, 'asc')->pluck('title')->all();

    expect($sorted)->toBe(['Low', 'High']);
});

it('renders as solid coloured text, not a translucent badge (house no-brown rule)', function (): void {
    expect(SeoScoreColumn::make()->isBadge())->toBeFalse();
});
