<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Filament\Livewire;

use AmjadIqbal\FilamentRankForge\Engine\ContentContext;
use AmjadIqbal\FilamentRankForge\Engine\Report;
use AmjadIqbal\FilamentRankForge\Engine\SeoAnalyzer;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * The live-updating analysis panel: score gauge, sub-scores, traffic-light
 * check list, desktop/mobile SERP preview, and social share card previews.
 *
 * SeoFields embeds this via Filament\Schemas\Components\Livewire, which
 * mounts it once with the form's initial values. Live re-analysis as the
 * user types happens over Livewire's standard cross-component event bus —
 * SeoFields dispatches 'rankforge-analysis-updated' from debounced field
 * updates, and refreshAnalysis() below picks it up — rather than relying on
 * Filament re-evaluating the embedded component's mount data on every
 * parent render, which isn't guaranteed.
 */
class AnalysisPanel extends Component
{
    public string $title = '';

    public string $description = '';

    public string $slug = '';

    public string $canonicalUrl = '';

    public string $contentHtml = '';

    public string $focusKeyphrase = '';

    /**
     * @var string[]
     */
    public array $relatedKeyphrases = [];

    public string $language = 'en';

    public bool $cornerstone = false;

    public string $ogTitle = '';

    public string $ogDescription = '';

    public string $ogImage = '';

    public string $serpDevice = 'desktop';

    /**
     * @param  string[]  $relatedKeyphrases
     */
    public function mount(
        string $title = '',
        string $description = '',
        string $slug = '',
        string $canonicalUrl = '',
        string $contentHtml = '',
        string $focusKeyphrase = '',
        array $relatedKeyphrases = [],
        string $language = 'en',
        bool $cornerstone = false,
        string $ogTitle = '',
        string $ogDescription = '',
        string $ogImage = '',
    ): void {
        $this->title = $title;
        $this->description = $description;
        $this->slug = $slug;
        $this->canonicalUrl = $canonicalUrl;
        $this->contentHtml = $contentHtml;
        $this->focusKeyphrase = $focusKeyphrase;
        $this->relatedKeyphrases = $relatedKeyphrases;
        $this->language = $language;
        $this->cornerstone = $cornerstone;
        $this->ogTitle = $ogTitle;
        $this->ogDescription = $ogDescription;
        $this->ogImage = $ogImage;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    #[On('rankforge-analysis-updated')]
    public function refreshAnalysis(array $data): void
    {
        $this->title = (string) ($data['title'] ?? $this->title);
        $this->description = (string) ($data['description'] ?? $this->description);
        $this->slug = (string) ($data['slug'] ?? $this->slug);
        $this->canonicalUrl = (string) ($data['canonicalUrl'] ?? $this->canonicalUrl);
        $this->contentHtml = (string) ($data['contentHtml'] ?? $this->contentHtml);
        $this->focusKeyphrase = (string) ($data['focusKeyphrase'] ?? $this->focusKeyphrase);
        $this->relatedKeyphrases = (array) ($data['relatedKeyphrases'] ?? $this->relatedKeyphrases);
        $this->language = (string) ($data['language'] ?? $this->language);
        $this->cornerstone = (bool) ($data['cornerstone'] ?? $this->cornerstone);
        $this->ogTitle = (string) ($data['ogTitle'] ?? $this->ogTitle);
        $this->ogDescription = (string) ($data['ogDescription'] ?? $this->ogDescription);
        $this->ogImage = (string) ($data['ogImage'] ?? $this->ogImage);
    }

    public function setSerpDevice(string $device): void
    {
        if (in_array($device, ['desktop', 'mobile'], true)) {
            $this->serpDevice = $device;
        }
    }

    public function toContentContext(): ContentContext
    {
        return new ContentContext(
            title: $this->title,
            description: $this->description,
            slug: $this->slug,
            canonicalUrl: $this->canonicalUrl,
            contentHtml: $this->contentHtml,
            focusKeyphrase: $this->focusKeyphrase,
            relatedKeyphrases: $this->relatedKeyphrases,
            language: $this->language,
            cornerstone: $this->cornerstone,
        );
    }

    public function report(): Report
    {
        return SeoAnalyzer::analyze($this->toContentContext());
    }

    public function render(): View
    {
        return view('rankforge::livewire.analysis-panel', [
            'report' => $this->report(),
            'title' => $this->title,
            'description' => $this->description,
            'slug' => $this->slug,
            'canonicalUrl' => $this->canonicalUrl,
            'serpDevice' => $this->serpDevice,
            'ogTitle' => $this->ogTitle,
            'ogDescription' => $this->ogDescription,
            'ogImage' => $this->ogImage,
            'redThreshold' => (int) config('rankforge.score_thresholds.red_below', 40),
            'greenThreshold' => (int) config('rankforge.score_thresholds.green_at_or_above', 70),
            'showUpgradeLink' => (bool) config('rankforge.lite.show_upgrade_link', true),
        ]);
    }
}
