{{--
    Styled with the scoped rfl-* stylesheet below rather than Tailwind
    utilities: a stock Filament 4/5 panel only ships the classes Filament itself
    uses, so arbitrary utilities in a plugin view would render unstyled unless
    the app builds a custom theme. The <style> block sits inside the root so it
    survives Livewire re-renders. Score colours come from Filament's own
    --danger/--warning/--success palette, used only as solid strokes, text and
    dots (never translucent fills).
--}}
@php
    $score = $report->score();
    $seoScore = $report->seoScore();
    $readabilityScore = $report->readabilityScore();

    $radius = 40;
    $circumference = 2 * M_PI * $radius;
    $gaugeOffset = $circumference - (($score ?? 0) / 100) * $circumference;

    $tone = fn (?int $value): string => match (true) {
        $value === null => 'none',
        $value < $redThreshold => 'bad',
        $value >= $greenThreshold => 'good',
        default => 'ok',
    };

    $displayTitle = $title !== '' ? $title : __('rankforge::rankforge.analysis.untitled');
    $displayDescription = $description !== '' ? $description : __('rankforge::rankforge.analysis.no_description');
    $displayUrl = $canonicalUrl !== '' ? $canonicalUrl : rtrim(url('/'), '/').($slug !== '' ? '/'.ltrim($slug, '/') : '');
    $urlParts = parse_url($displayUrl) ?: [];
    $host = $urlParts['host'] ?? parse_url(url('/'), PHP_URL_HOST) ?? '';
    $breadcrumb = trim(str_replace('/', ' › ', trim($urlParts['path'] ?? '', '/')));

    $shareTitle = $ogTitle !== '' ? $ogTitle : $displayTitle;
    $shareDescription = $ogDescription !== '' ? $ogDescription : $displayDescription;
    $shareImage = $ogImage !== '' ? $ogImage : (string) config('rankforge.frontend.default_og_image', '');
@endphp

<div class="rfl">
    <style>
        .rfl { --rfl-border: rgb(229 231 235); --rfl-muted: rgb(107 114 128); --rfl-soft: rgb(249 250 251); --rfl-track: rgb(229 231 235); --rfl-link: #1a0dab;
            --rfl-good: var(--success-500, #10b981); --rfl-ok: var(--warning-500, #eab308); --rfl-bad: var(--danger-500, #ef4444);
            border: 1px solid var(--rfl-border); border-radius: .75rem; padding: 1rem; display: grid; gap: 1.5rem; font-size: .875rem; }
        .dark .rfl { --rfl-border: rgb(255 255 255 / .1); --rfl-muted: rgb(156 163 175); --rfl-soft: rgb(255 255 255 / .05); --rfl-track: rgb(255 255 255 / .1); --rfl-link: #8ab4f8; }
        .rfl-head { display: flex; align-items: center; gap: 1.5rem; }
        .rfl-gauge { position: relative; width: 6rem; height: 6rem; flex-shrink: 0; }
        .rfl-gauge svg { width: 6rem; height: 6rem; transform: rotate(-90deg); }
        .rfl-gauge-num { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 1.375rem; font-weight: 700; }
        .rfl-subs { flex: 1; display: grid; gap: .5rem; }
        .rfl-sub { display: flex; justify-content: space-between; }
        .rfl-sub b { font-weight: 600; }
        .rfl-tone-good { color: var(--rfl-good); } .rfl-tone-ok { color: var(--rfl-ok); } .rfl-tone-bad { color: var(--rfl-bad); } .rfl-tone-none { color: var(--rfl-muted); }
        .rfl-dot { width: .625rem; height: .625rem; border-radius: 9999px; display: inline-block; }
        .rfl-dot.rfl-tone-good { background: var(--rfl-good); } .rfl-dot.rfl-tone-ok { background: var(--rfl-ok); } .rfl-dot.rfl-tone-bad { background: var(--rfl-bad); }
        .rfl-groups { display: grid; gap: .5rem; }
        .rfl-group-btn { width: 100%; display: flex; align-items: center; justify-content: space-between; padding: .5rem .75rem; border-radius: .5rem; background: var(--rfl-soft); font-weight: 500; cursor: pointer; }
        .rfl-group-btn > span:first-child { display: flex; align-items: center; gap: .5rem; }
        .rfl-list { margin: .5rem 0 0; padding-inline-start: 1.75rem; list-style: disc; color: var(--rfl-muted); display: grid; gap: .25rem; }
        .rfl-tabs { display: inline-flex; gap: .25rem; margin-bottom: .5rem; }
        .rfl-tab { padding: .25rem .625rem; border-radius: .375rem; background: var(--rfl-soft); cursor: pointer; }
        .rfl-tab[aria-pressed="true"] { background: var(--primary-600, #4f46e5); color: #fff; }
        .rfl-serp { border: 1px solid var(--rfl-border); border-radius: .5rem; padding: .75rem 1rem; font-family: Arial, Helvetica, sans-serif; max-width: 600px; }
        .rfl-serp[data-device="mobile"] { max-width: 360px; }
        .rfl-serp-site { display: flex; align-items: center; gap: .5rem; font-size: .75rem; line-height: 1.3; min-width: 0; }
        .rfl-serp-site > span:last-child { min-width: 0; display: grid; }
        .rfl-serp-fav { width: 1.625rem; height: 1.625rem; border-radius: 9999px; background: var(--rfl-soft); border: 1px solid var(--rfl-border); flex-shrink: 0; }
        .rfl-serp-url { color: var(--rfl-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .rfl-serp-title { color: var(--rfl-link); font-size: 1.25rem; line-height: 1.3; margin-top: .375rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .rfl-serp[data-device="mobile"] .rfl-serp-title { white-space: normal; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; font-size: 1.125rem; }
        .rfl-serp-desc { color: var(--rfl-muted); line-height: 1.55; margin-top: .25rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .rfl-cards { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(11rem, 1fr)); }
        .rfl-card { border: 1px solid var(--rfl-border); border-radius: .5rem; overflow: hidden; min-width: 0; }
        .rfl-card-img { aspect-ratio: 1.91 / 1; background: var(--rfl-soft); display: flex; align-items: center; justify-content: center; color: var(--rfl-muted); font-size: .75rem; }
        .rfl-card-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .rfl-card-body { padding: .5rem .625rem; display: grid; gap: .125rem; }
        .rfl-card-net { font-size: .6875rem; text-transform: uppercase; color: var(--rfl-muted); }
        .rfl-card-title { font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .rfl-card-desc { font-size: .75rem; color: var(--rfl-muted); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .rfl-upgrade { font-size: .75rem; color: var(--rfl-muted); }
        .rfl-upgrade a { color: var(--primary-600, #4f46e5); text-decoration: underline; text-underline-offset: 2px; }
        .dark .rfl-upgrade a { color: var(--primary-400, #818cf8); }
    </style>

    {{-- Score gauge + sub-scores --}}
    <div class="rfl-head">
        <div class="rfl-gauge" role="img" aria-label="{{ __('rankforge::rankforge.seo_score') }}: {{ $score ?? '—' }}">
            <svg viewBox="0 0 100 100" aria-hidden="true">
                <circle cx="50" cy="50" r="{{ $radius }}" fill="none" stroke-width="8" stroke="var(--rfl-track)" />
                <circle cx="50" cy="50" r="{{ $radius }}" fill="none" stroke-width="8" stroke-linecap="round"
                    class="rfl-tone-{{ $tone($score) }}" stroke="currentColor"
                    stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $gaugeOffset }}" />
            </svg>
            <div class="rfl-gauge-num">{{ $score ?? '—' }}</div>
        </div>

        <div class="rfl-subs">
            <div class="rfl-sub">
                <span>{{ __('rankforge::rankforge.analysis.seo_score') }}</span>
                <b class="rfl-tone-{{ $tone($seoScore) }}">{{ $seoScore ?? '—' }}</b>
            </div>
            <div class="rfl-sub">
                <span>{{ __('rankforge::rankforge.analysis.readability_score') }}</span>
                <b class="rfl-tone-{{ $tone($readabilityScore) }}">{{ $readabilityScore ?? '—' }}</b>
            </div>
        </div>
    </div>

    {{-- Traffic-light check list --}}
    <div class="rfl-groups">
        @foreach ([
            'failed' => ['label' => __('rankforge::rankforge.analysis.failed'), 'results' => $report->failed(), 'tone' => 'bad'],
            'warnings' => ['label' => __('rankforge::rankforge.analysis.warnings'), 'results' => $report->warnings(), 'tone' => 'ok'],
            'passed' => ['label' => __('rankforge::rankforge.analysis.passed'), 'results' => $report->passed(), 'tone' => 'good'],
        ] as $groupKey => $group)
            @if (count($group['results']))
                <div x-data="{ expanded: {{ $groupKey === 'failed' ? 'true' : 'false' }} }" wire:key="rfl-group-{{ $groupKey }}">
                    <button type="button" x-on:click="expanded = ! expanded" class="rfl-group-btn" x-bind:aria-expanded="expanded">
                        <span><span class="rfl-dot rfl-tone-{{ $group['tone'] }}"></span>{{ $group['label'] }} ({{ count($group['results']) }})</span>
                        <span x-text="expanded ? '−' : '+'"></span>
                    </button>
                    <ul x-show="expanded" x-cloak class="rfl-list">
                        @foreach ($group['results'] as $result)
                            <li>{{ $result->message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach
    </div>

    {{-- SERP preview --}}
    <div>
        <div class="rfl-tabs">
            <button type="button" class="rfl-tab" wire:click="setSerpDevice('desktop')" aria-pressed="{{ $serpDevice === 'desktop' ? 'true' : 'false' }}">
                {{ __('rankforge::rankforge.analysis.desktop') }}
            </button>
            <button type="button" class="rfl-tab" wire:click="setSerpDevice('mobile')" aria-pressed="{{ $serpDevice === 'mobile' ? 'true' : 'false' }}">
                {{ __('rankforge::rankforge.analysis.mobile') }}
            </button>
        </div>

        <div class="rfl-serp" data-device="{{ $serpDevice }}">
            <div class="rfl-serp-site">
                <span class="rfl-serp-fav" aria-hidden="true"></span>
                <span>
                    <span>{{ config('rankforge.frontend.site_name') ?: $host }}</span>
                    <span class="rfl-serp-url">{{ $host }}{{ $breadcrumb !== '' ? ' › '.$breadcrumb : '' }}</span>
                </span>
            </div>
            <div class="rfl-serp-title">{{ $displayTitle }}</div>
            <div class="rfl-serp-desc">{{ $displayDescription }}</div>
        </div>
    </div>

    {{-- Social share previews (Open Graph + X card) --}}
    <div class="rfl-cards">
        @foreach (['facebook', 'x', 'linkedin'] as $network)
            <div class="rfl-card" wire:key="rfl-card-{{ $network }}">
                <div class="rfl-card-img">
                    @if ($shareImage !== '')
                        <img src="{{ $shareImage }}" alt="" loading="lazy">
                    @else
                        {{ __('rankforge::rankforge.analysis.no_image') }}
                    @endif
                </div>
                <div class="rfl-card-body">
                    <span class="rfl-card-net">{{ __("rankforge::rankforge.analysis.{$network}") }}</span>
                    <span class="rfl-card-title">{{ $shareTitle }}</span>
                    <span class="rfl-card-desc">{{ $shareDescription }}</span>
                </div>
            </div>
        @endforeach
    </div>

    @if ($showUpgradeLink)
        <p class="rfl-upgrade">
            <a href="https://rankforge.amjad.com.pk/?utm_source=rankforge-lite&amp;utm_medium=plugin&amp;utm_campaign=analysis-panel" target="_blank" rel="noopener">{{ __('rankforge::rankforge.analysis.upgrade_link') }}</a>
        </p>
    @endif
</div>
