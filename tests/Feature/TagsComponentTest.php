<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Tests\Fixtures\Post;
use AmjadIqbal\FilamentRankForge\View\Components\Tags;
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

it('renders static-mode title, description, and robots meta tags', function (): void {
    $html = (new Tags(title: 'Contact', description: 'Get in touch.', robots: ['noindex', 'nofollow']))
        ->render()
        ->render();

    expect($html)->toContain('<title>Contact</title>')
        ->and($html)->toContain('name="description" content="Get in touch."')
        ->and($html)->toContain('name="robots" content="noindex, nofollow"');
});

it('resolves title/description from the model via toSeoContentContext() when no static props are given', function (): void {
    $post = Post::create([
        'title' => 'Best Coffee Beans',
        'slug' => 'best-coffee-beans',
        'content' => '<p>Coffee content.</p>',
    ]);

    $html = (new Tags(model: $post))->render()->render();

    expect($html)->toContain('<title>Best Coffee Beans</title>');
});

it('prefers an explicit rankforge_meta override over the model fallback', function (): void {
    $post = Post::create([
        'title' => 'Best Coffee Beans',
        'slug' => 'best-coffee-beans',
        'content' => '<p>Coffee content.</p>',
    ]);
    $post->rankforgeMeta()->update(['meta_title' => 'Custom Meta Title']);
    $post->refresh();

    $html = (new Tags(model: $post))->render()->render();

    expect($html)->toContain('<title>Custom Meta Title</title>')
        ->and($html)->not->toContain('Best Coffee Beans');
});

it('falls back og:title/og:description to the resolved title/description when not overridden', function (): void {
    $html = (new Tags(title: 'Contact', description: 'Get in touch.'))->render()->render();

    expect($html)->toContain('property="og:title" content="Contact"')
        ->and($html)->toContain('property="og:description" content="Get in touch."');
});

it('never renders a JSON-LD script tag (structured data is a RankForge feature)', function (): void {
    $html = (new Tags(title: 'Home'))->render()->render();

    expect($html)->not->toContain('application/ld+json');
});

it('renders hreflang alternate links for every locale given', function (): void {
    $html = (new Tags(title: 'Home', hreflang: ['en' => 'https://example.test/en', 'es' => 'https://example.test/es']))
        ->render()
        ->render();

    expect($html)->toContain('hreflang="en" href="https://example.test/en"')
        ->and($html)->toContain('hreflang="es" href="https://example.test/es"');
});

it('renders via Tags::renderFor() the same way the @rankforge directive invokes it', function (): void {
    $html = (string) Tags::renderFor(['title' => 'Contact', 'description' => 'Get in touch.']);

    expect($html)->toContain('<title>Contact</title>');
});

it('renders via Tags::renderFor() when passed a model directly, as @rankforge($post) would', function (): void {
    $post = Post::create([
        'title' => 'Best Coffee Beans',
        'slug' => 'best-coffee-beans',
        'content' => '<p>Coffee content.</p>',
    ]);

    $html = (string) Tags::renderFor($post);

    expect($html)->toContain('<title>Best Coffee Beans</title>');
});

it('compiles the @rankforge directive into a call to Tags::renderFor()', function (): void {
    $compiled = app('blade.compiler')->compileString('@rankforge($post)');

    expect($compiled)->toContain('\\'.Tags::class.'::renderFor($post)');
});

it('auto-generates hreflang tags from a duck-typed getSeoLocaleUrls() when no explicit prop is given', function (): void {
    $post = new class extends Post
    {
        public function getSeoLocaleUrls(): array
        {
            return ['en' => 'https://example.test/en/coffee', 'es' => 'https://example.test/es/coffee'];
        }
    };
    $post->setTable('posts');
    $post->fill(['title' => 'Coffee', 'slug' => 'coffee', 'content' => '<p>Content.</p>'])->save();

    $html = (new Tags(model: $post))->render()->render();

    expect($html)->toContain('hreflang="en" href="https://example.test/en/coffee"')
        ->and($html)->toContain('hreflang="es" href="https://example.test/es/coffee"');
});

it('lets an explicit hreflang prop override a model-provided getSeoLocaleUrls()', function (): void {
    $post = new class extends Post
    {
        public function getSeoLocaleUrls(): array
        {
            return ['en' => 'https://example.test/en/coffee'];
        }
    };
    $post->setTable('posts');
    $post->fill(['title' => 'Coffee', 'slug' => 'coffee', 'content' => '<p>Content.</p>'])->save();

    $html = (new Tags(model: $post, hreflang: ['fr' => 'https://example.test/fr/coffee']))->render()->render();

    expect($html)->toContain('hreflang="fr" href="https://example.test/fr/coffee"')
        ->and($html)->not->toContain('hreflang="en"');
});

it('resolves the meta title/description from a locale translation when one is stored', function (): void {
    $post = Post::create(['title' => 'Coffee', 'slug' => 'coffee', 'content' => '<p>Content.</p>']);
    $post->rankforgeMeta->update([
        'meta_title' => 'Coffee (default)',
        'meta_title_translations' => ['es' => 'Café (español)'],
    ]);
    $post->unsetRelation('rankforgeMeta');

    app()->setLocale('es');
    $html = (new Tags(model: $post))->render()->render();
    expect($html)->toContain('<title>Café (español)</title>');

    app()->setLocale('en');
    $html = (new Tags(model: $post))->render()->render();
    expect($html)->toContain('<title>Coffee (default)</title>');
});

it('falls back og:image to the stored override, then the site-wide default', function (): void {
    config()->set('rankforge.frontend.default_og_image', 'https://example.test/default.png');

    $post = Post::create(['title' => 'Coffee', 'slug' => 'coffee', 'content' => '<p>Content.</p>']);
    expect((new Tags(model: $post))->render()->render())->toContain('property="og:image" content="https://example.test/default.png"');

    $post->rankforgeMeta->update(['og_image' => 'https://example.test/coffee.png']);
    $post->unsetRelation('rankforgeMeta');
    expect((new Tags(model: $post))->render()->render())->toContain('property="og:image" content="https://example.test/coffee.png"');
});

it('renders X (Twitter) card tags that follow the Open Graph values', function (): void {
    config()->set('rankforge.frontend.twitter_site', '@acme');

    $html = (new Tags(title: 'Contact', description: 'Get in touch.', ogImage: 'https://example.test/c.png'))->render()->render();

    expect($html)->toContain('name="twitter:card" content="summary_large_image"')
        ->and($html)->toContain('name="twitter:site" content="@acme"')
        ->and($html)->toContain('name="twitter:title" content="Contact"')
        ->and($html)->toContain('name="twitter:description" content="Get in touch."')
        ->and($html)->toContain('name="twitter:image" content="https://example.test/c.png"');
});

it('uses the small summary X card when there is no image', function (): void {
    $html = (new Tags(title: 'Contact'))->render()->render();

    expect($html)->toContain('name="twitter:card" content="summary"')
        ->and($html)->not->toContain('twitter:image');
});
