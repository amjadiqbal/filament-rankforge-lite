<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Filament\Forms;

use AmjadIqbal\FilamentRankForge\Filament\Livewire\AnalysisPanel;
use Closure;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Livewire;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Livewire\Component as LivewireComponent;

/**
 * Renders the SEO input fields plus the embedded live AnalysisPanel.
 * contentField/slugField (or the getContentUsing/getSlugUsing overrides) tell
 * it how to read the sibling content/slug fields outside this Section:
 *
 *   SeoFields::make(contentField: 'body', slugField: 'permalink');
 *
 * The Section is bound to the model's rankforgeMeta() morphOne relation (see
 * Concerns/HasSeo) via ->relationship('rankforgeMeta'), so the fields load
 * from and save to the rankforge_meta row. Pass relationship: null to opt out
 * and manage persistence yourself.
 *
 * Live re-analysis: the fields owned by this Section are ->live(debounce: 600)
 * and dispatch 'rankforge-analysis-updated', which AnalysisPanel listens for.
 * Sibling content/slug fields are not live by default; give them
 * ->live(debounce: 600) and ->afterStateUpdated(SeoFields::refreshAnalysis())
 * to make the panel follow them too.
 */
final class SeoFields
{
    public const ANALYSIS_UPDATED_EVENT = 'rankforge-analysis-updated';

    /**
     * @var string[]
     */
    private const ROBOTS_OPTIONS = [
        'index',
        'noindex',
        'follow',
        'nofollow',
        'noarchive',
        'nosnippet',
        'max-snippet',
        'max-image-preview',
    ];

    /**
     * @var array<string, string>
     */
    private const LANGUAGE_OPTIONS = [
        'en' => 'English',
        'es' => 'Español',
        'fr' => 'Français',
        'de' => 'Deutsch',
        'ar' => 'العربية',
        'ur' => 'اردو',
    ];

    public static function make(
        string $contentField = 'content',
        string $slugField = 'slug',
        ?Closure $getContentUsing = null,
        ?Closure $getSlugUsing = null,
        ?string $relationship = 'rankforgeMeta',
    ): Section {
        $getContentUsing ??= static fn (Get $get): string => self::contentHtml($get("../{$contentField}"));
        $getSlugUsing ??= static fn (Get $get): string => (string) $get("../{$slugField}");

        $dispatchUpdate = static function (Get $get, LivewireComponent $livewire) use ($getContentUsing, $getSlugUsing): void {
            $livewire->dispatch(self::ANALYSIS_UPDATED_EVENT, data: self::analysisData($get, $getContentUsing, $getSlugUsing));
        };

        $section = Section::make(__('rankforge::rankforge.fields.section_heading'))
            ->collapsible()
            ->schema([
                TextInput::make('focus_keyphrase')
                    ->label(__('rankforge::rankforge.fields.focus_keyphrase'))
                    ->maxLength(255)
                    ->live(debounce: 600)
                    ->afterStateUpdated($dispatchUpdate),

                TagsInput::make('related_keyphrases')
                    ->label(__('rankforge::rankforge.fields.related_keyphrases'))
                    ->helperText(__('rankforge::rankforge.fields.related_keyphrases_helper'))
                    ->reorderable()
                    ->rules(['array', 'max:4'])
                    ->live(debounce: 600)
                    ->afterStateUpdated($dispatchUpdate),

                TextInput::make('meta_title')
                    ->label(__('rankforge::rankforge.fields.meta_title'))
                    ->maxLength(255)
                    ->live(debounce: 600)
                    ->afterStateUpdated($dispatchUpdate),

                Textarea::make('meta_description')
                    ->label(__('rankforge::rankforge.fields.meta_description'))
                    ->rows(3)
                    ->maxLength(320)
                    ->live(debounce: 600)
                    ->afterStateUpdated($dispatchUpdate),

                TextInput::make('canonical_url')
                    ->label(__('rankforge::rankforge.fields.canonical_url'))
                    ->url()
                    ->maxLength(255)
                    ->live(debounce: 600)
                    ->afterStateUpdated($dispatchUpdate),

                CheckboxList::make('robots')
                    ->label(__('rankforge::rankforge.fields.robots'))
                    ->options(self::robotsOptions())
                    ->columns(2),

                Section::make(__('rankforge::rankforge.fields.og_section_heading'))
                    ->description(__('rankforge::rankforge.fields.og_section_description'))
                    ->collapsed()
                    ->schema([
                        TextInput::make('og_title')
                            ->label(__('rankforge::rankforge.fields.og_title'))
                            ->maxLength(255)
                            ->live(debounce: 600)
                            ->afterStateUpdated($dispatchUpdate),
                        Textarea::make('og_description')
                            ->label(__('rankforge::rankforge.fields.og_description'))
                            ->rows(2)
                            ->live(debounce: 600)
                            ->afterStateUpdated($dispatchUpdate),
                        TextInput::make('og_image')
                            ->label(__('rankforge::rankforge.fields.og_image'))
                            ->url()
                            ->maxLength(255)
                            ->live(debounce: 600)
                            ->afterStateUpdated($dispatchUpdate),
                    ]),

                Toggle::make('cornerstone')
                    ->label(__('rankforge::rankforge.fields.cornerstone'))
                    ->live()
                    ->afterStateUpdated($dispatchUpdate),

                Select::make('language')
                    ->label(__('rankforge::rankforge.fields.language'))
                    ->options(self::LANGUAGE_OPTIONS)
                    ->default(config('rankforge.analysis.default_language', 'en'))
                    ->live()
                    ->afterStateUpdated($dispatchUpdate),

                Livewire::make(
                    AnalysisPanel::class,
                    static fn (Get $get): array => self::analysisData($get, $getContentUsing, $getSlugUsing),
                ),
            ]);

        if ($relationship !== null) {
            $section->relationship($relationship);
        }

        return $section;
    }

    /**
     * An afterStateUpdated() callback for fields outside the SEO section (the
     * content or slug field), so the analysis panel follows them as they change:
     *
     *   RichEditor::make('content')->live(debounce: 600)->afterStateUpdated(SeoFields::refreshAnalysis())
     *
     * The callback runs in the sibling field's own scope, so the section's
     * fields are read through the relationship's statePath.
     */
    public static function refreshAnalysis(string $contentField = 'content', string $slugField = 'slug', string $relationship = 'rankforgeMeta'): Closure
    {
        return static function (Get $get, LivewireComponent $livewire) use ($contentField, $slugField, $relationship): void {
            $field = static fn (string $name): mixed => $get("{$relationship}.{$name}");

            $livewire->dispatch(self::ANALYSIS_UPDATED_EVENT, data: [
                'title' => (string) ($field('meta_title') ?? ''),
                'description' => (string) ($field('meta_description') ?? ''),
                'slug' => (string) ($get($slugField) ?? ''),
                'canonicalUrl' => (string) ($field('canonical_url') ?? ''),
                'contentHtml' => self::contentHtml($get($contentField)),
                'focusKeyphrase' => (string) ($field('focus_keyphrase') ?? ''),
                'relatedKeyphrases' => (array) ($field('related_keyphrases') ?? []),
                'language' => (string) ($field('language') ?: config('rankforge.analysis.default_language', 'en')),
                'cornerstone' => (bool) $field('cornerstone'),
                'ogTitle' => (string) ($field('og_title') ?? ''),
                'ogDescription' => (string) ($field('og_description') ?? ''),
                'ogImage' => (string) ($field('og_image') ?? ''),
            ]);
        };
    }

    /**
     * Content state as HTML: a RichEditor that stores JSON holds a TipTap
     * document array rather than an HTML string.
     */
    public static function contentHtml(mixed $state): string
    {
        if (is_array($state)) {
            return RichContentRenderer::make($state)->toHtml();
        }

        return is_scalar($state) ? (string) $state : '';
    }

    /**
     * @return array<string, mixed>
     */
    private static function analysisData(Get $get, Closure $getContentUsing, Closure $getSlugUsing): array
    {
        return [
            'title' => (string) ($get('meta_title') ?? ''),
            'description' => (string) ($get('meta_description') ?? ''),
            'slug' => (string) ($getSlugUsing($get) ?? ''),
            'canonicalUrl' => (string) ($get('canonical_url') ?? ''),
            'contentHtml' => self::contentHtml($getContentUsing($get)),
            'focusKeyphrase' => (string) ($get('focus_keyphrase') ?? ''),
            'relatedKeyphrases' => (array) ($get('related_keyphrases') ?? []),
            'language' => (string) ($get('language') ?: config('rankforge.analysis.default_language', 'en')),
            'cornerstone' => (bool) $get('cornerstone'),
            'ogTitle' => (string) ($get('og_title') ?? ''),
            'ogDescription' => (string) ($get('og_description') ?? ''),
            'ogImage' => (string) ($get('og_image') ?? ''),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function robotsOptions(): array
    {
        $options = [];

        foreach (self::ROBOTS_OPTIONS as $value) {
            $options[$value] = __("rankforge::rankforge.robots_options.{$value}");
        }

        return $options;
    }
}
