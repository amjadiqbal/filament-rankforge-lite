<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Filament\Forms;

use Closure;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\RichEditor\RichContentRenderer;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\View;

/**
 * Renders the SEO input fields plus the live analysis panel. titleField,
 * contentField and slugField (or the get*Using overrides) tell it how to read
 * the sibling fields outside this Section:
 *
 *   SeoFields::make(titleField: 'headline', contentField: 'body', slugField: 'permalink');
 *
 * The Section is bound to the model's rankforgeMeta() morphOne relation (see
 * Concerns/HasSeo) via ->relationship('rankforgeMeta'), so the fields load
 * from and save to the rankforge_meta row. Pass relationship: null to opt out
 * and manage persistence yourself.
 *
 * Live analysis: the SEO fields are ->live(debounce: 600) and the panel is a
 * schema view that recomputes on each form render. Make the sibling title,
 * content and slug fields ->live(debounce: 600) too if the panel should follow
 * them as you type; otherwise it catches up on their next change.
 */
final class SeoFields
{
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
        string $titleField = 'title',
    ): Section {
        $getContentUsing ??= static fn (Get $get): string => self::contentHtml($get("../{$contentField}"));
        $getSlugUsing ??= static fn (Get $get): string => (string) $get("../{$slugField}");
        $getTitleUsing = static fn (Get $get): string => (string) $get("../{$titleField}");

        $section = Section::make(__('rankforge::rankforge.fields.section_heading'))
            ->collapsible()
            ->schema([
                TextInput::make('focus_keyphrase')
                    ->label(__('rankforge::rankforge.fields.focus_keyphrase'))
                    ->maxLength(255)
                    ->live(debounce: 600),

                TagsInput::make('related_keyphrases')
                    ->label(__('rankforge::rankforge.fields.related_keyphrases'))
                    ->helperText(__('rankforge::rankforge.fields.related_keyphrases_helper'))
                    ->reorderable()
                    ->rules(['array', 'max:4'])
                    ->live(debounce: 600),

                TextInput::make('meta_title')
                    ->label(__('rankforge::rankforge.fields.meta_title'))
                    ->maxLength(255)
                    ->live(debounce: 600),

                Textarea::make('meta_description')
                    ->label(__('rankforge::rankforge.fields.meta_description'))
                    ->rows(3)
                    ->maxLength(320)
                    ->live(debounce: 600),

                TextInput::make('canonical_url')
                    ->label(__('rankforge::rankforge.fields.canonical_url'))
                    ->url()
                    ->maxLength(255)
                    ->live(debounce: 600),

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
                            ->live(debounce: 600),
                        Textarea::make('og_description')
                            ->label(__('rankforge::rankforge.fields.og_description'))
                            ->rows(2)
                            ->live(debounce: 600),
                        TextInput::make('og_image')
                            ->label(__('rankforge::rankforge.fields.og_image'))
                            ->url()
                            ->maxLength(255)
                            ->live(debounce: 600),
                    ]),

                Toggle::make('cornerstone')
                    ->label(__('rankforge::rankforge.fields.cornerstone'))
                    ->live(),

                Select::make('language')
                    ->label(__('rankforge::rankforge.fields.language'))
                    ->options(self::LANGUAGE_OPTIONS)
                    ->default(config('rankforge.analysis.default_language', 'en'))
                    ->live(),

                View::make(AnalysisPanel::VIEW)
                    ->viewData(static fn (Get $get): array => AnalysisPanel::viewData(
                        self::analysisData($get, $getContentUsing, $getSlugUsing, $getTitleUsing),
                    )),
            ]);

        if ($relationship !== null) {
            $section->relationship($relationship);
        }

        return $section;
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
    private static function analysisData(Get $get, Closure $getContentUsing, Closure $getSlugUsing, Closure $getTitleUsing): array
    {
        // Same fallback as HasSeo::toSeoContentContext(): the record's own title when no meta title is set.
        return [
            'title' => (string) ($get('meta_title') ?: $getTitleUsing($get)),
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
