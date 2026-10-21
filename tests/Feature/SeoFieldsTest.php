<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Filament\Forms\SeoFields;
use AmjadIqbal\FilamentRankForge\Tests\Fixtures\Livewire\SeoFieldsTestComponent;
use Filament\Schemas\Components\Section;

/**
 * SeoFields::make() returns a bare Section, which throws once you try to
 * walk its children/actions — Filament components only initialize their
 * container/livewire context once attached to a schema owned by a real
 * HasSchemas Livewire component. A directly-instantiated (not mounted via
 * Livewire::test(), which drags in the full HTTP/session/validation
 * lifecycle we don't need here) SeoFieldsTestComponent is enough to
 * satisfy that requirement for structural assertions.
 */
function mountedSeoFieldsSection(): Section
{
    $component = new SeoFieldsTestComponent;

    return $component->getSchema('form')->getComponents()[0];
}

function seoFieldNames(): array
{
    return collect(mountedSeoFieldsSection()->getChildComponents())
        ->map(fn ($component) => method_exists($component, 'getName') ? $component->getName() : null)
        ->filter()
        ->values()
        ->all();
}

it('returns a Section', function (): void {
    expect(mountedSeoFieldsSection())->toBeInstanceOf(Section::class);
});

it('includes all documented top-level fields', function (): void {
    expect(seoFieldNames())->toContain(
        'focus_keyphrase',
        'related_keyphrases',
        'meta_title',
        'meta_description',
        'canonical_url',
        'robots',
        'cornerstone',
        'language',
    );
});

it('nests the OG override fields inside a collapsed sub-section', function (): void {
    $ogSection = collect(mountedSeoFieldsSection()->getChildComponents())
        ->first(fn ($component) => $component instanceof Section);

    expect($ogSection)->not->toBeNull();

    $ogFieldNames = collect($ogSection->getChildComponents())
        ->map(fn ($component) => $component->getName())
        ->all();

    expect($ogFieldNames)->toBe(['og_title', 'og_description', 'og_image']);
});

it('ships no paid-only fields or actions', function (): void {
    expect(seoFieldNames())->not->toContain('schema_type')
        ->and(mountedSeoFieldsSection()->getHeaderActions())->toBe([]);
});

it('translates the robots checkbox options', function (): void {
    $robots = collect(mountedSeoFieldsSection()->getChildComponents())
        ->first(fn ($component) => method_exists($component, 'getName') && $component->getName() === 'robots');

    expect($robots->getOptions())->toBe([
        'index' => 'Index',
        'noindex' => 'No index',
        'follow' => 'Follow',
        'nofollow' => 'No follow',
        'noarchive' => 'No archive',
        'nosnippet' => 'No snippet',
        'max-snippet' => 'Max snippet',
        'max-image-preview' => 'Max image preview',
    ]);
});

it('reads RichEditor JSON state as HTML for the analysis', function (): void {
    $doc = ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Hello espresso']]]]];

    expect(SeoFields::contentHtml($doc))->toContain('<p>Hello espresso</p>')
        ->and(SeoFields::contentHtml('<p>Hi</p>'))->toBe('<p>Hi</p>')
        ->and(SeoFields::contentHtml(null))->toBe('');
});
