<?php

declare(strict_types=1);

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

function makeMeta(array $attributes = []): RankForgeMeta
{
    $post = Post::create(['title' => 'Coffee', 'slug' => 'coffee']);

    return $post->rankforgeMeta()->updateOrCreate([], $attributes);
}

it('falls back to the plain meta_title when no translation exists for the requested locale', function (): void {
    $meta = makeMeta(['meta_title' => 'Coffee Guide']);

    expect($meta->translatedMetaTitle('es'))->toBe('Coffee Guide');
});

it('returns the stored translation for the requested locale', function (): void {
    $meta = makeMeta([
        'meta_title' => 'Coffee Guide',
        'meta_title_translations' => ['es' => 'Guía de Café'],
    ]);

    expect($meta->translatedMetaTitle('es'))->toBe('Guía de Café')
        ->and($meta->translatedMetaTitle('en'))->toBe('Coffee Guide');
});

it('defaults to the current app locale when none is given', function (): void {
    $meta = makeMeta([
        'meta_title' => 'Coffee Guide',
        'meta_title_translations' => ['es' => 'Guía de Café'],
    ]);

    app()->setLocale('es');

    expect($meta->translatedMetaTitle())->toBe('Guía de Café');
});

it('does the same for meta_description', function (): void {
    $meta = makeMeta([
        'meta_description' => 'A guide to coffee.',
        'meta_description_translations' => ['es' => 'Una guía de café.'],
    ]);

    expect($meta->translatedMetaDescription('es'))->toBe('Una guía de café.')
        ->and($meta->translatedMetaDescription('en'))->toBe('A guide to coffee.');
});

it('setTranslatedMetaTitle() adds a locale without clobbering existing ones', function (): void {
    $meta = makeMeta(['meta_title_translations' => ['es' => 'Guía de Café']]);

    $meta->setTranslatedMetaTitle('fr', 'Guide du Café');

    expect($meta->meta_title_translations)->toBe(['es' => 'Guía de Café', 'fr' => 'Guide du Café']);
});

it('returns null when there is neither a translation nor a plain meta_title', function (): void {
    $meta = makeMeta();

    expect($meta->translatedMetaTitle('es'))->toBeNull();
});
