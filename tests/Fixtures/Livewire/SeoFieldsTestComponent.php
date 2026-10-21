<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Tests\Fixtures\Livewire;

use AmjadIqbal\FilamentRankForge\Filament\Forms\SeoFields;
use AmjadIqbal\FilamentRankForge\Tests\Fixtures\Post;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Minimal Livewire host used to exercise Filament schema components in
 * tests/Feature — Filament's schema testing helpers (assertSchemaComponentExists
 * etc.) require a real HasSchemas Livewire component, not a bare Schema.
 *
 * SeoFields::make() binds to the rankforgeMeta relationship by default,
 * which needs a real model instance to resolve against — a fresh (unsaved)
 * Post fixture is enough since these tests only assert on structure, not
 * on an actual save/reload round-trip.
 */
class SeoFieldsTestComponent extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    public function form(Schema $schema): Schema
    {
        return $schema->model(Post::class)->components([SeoFields::make()]);
    }

    public function render(): View
    {
        return view('rankforge-testing::seo-fields-test-component');
    }
}
