<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge;

use AmjadIqbal\FilamentRankForge\Models\RankForgeMeta;
use AmjadIqbal\FilamentRankForge\View\Components\Tags;
use Composer\InstalledVersions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Console\AboutCommand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class RankForgeServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        // Migration names match the full RankForge suite exactly, so after an
        // upgrade Laravel sees them as already run and the data stays put.
        $package
            ->name('rankforge')
            ->hasConfigFile('rankforge')
            ->hasMigrations([
                'create_rankforge_meta_table',
                'create_rankforge_meta_translations_columns',
                'widen_rankforge_meta_url_columns',
            ])
            ->runsMigrations()
            ->hasTranslations()
            ->hasViews();
    }

    public function packageBooted(): void
    {
        Blade::componentNamespace('AmjadIqbal\\FilamentRankForge\\View\\Components', 'rankforge');

        Blade::directive('rankforge', static fn (string $expression): string => '<?php echo \\'.Tags::class."::renderFor({$expression}); ?>");

        $this->registerScoreSync();

        if (class_exists(AboutCommand::class)) {
            AboutCommand::add('RankForge Lite', static fn (): array => [
                'Version' => InstalledVersions::isInstalled('amjadiqbal/filament-rankforge-lite')
                    ? (string) InstalledVersions::getPrettyVersion('amjadiqbal/filament-rankforge-lite')
                    : 'unknown',
                'Full suite' => 'https://rankforge.amjad.com.pk',
                'Custom work' => 'https://amjad.com.pk/contact?utm_source=artisan-about&utm_medium=package&utm_campaign=filament-rankforge-lite',
            ]);
        }
    }

    /**
     * Keeps stored scores current without a trait boot method (see HasSeo):
     * a record using HasSeo is re-scored after it saves, and again after its
     * rankforge_meta row saves, since Filament saves the SEO fields as a
     * relationship after the record itself.
     */
    private function registerScoreSync(): void
    {
        Event::listen('eloquent.saved: *', static function (string $event, array $payload): void {
            $model = $payload[0] ?? null;

            if ($model instanceof Model && ! $model instanceof RankForgeMeta && method_exists($model, 'syncSeoScore')) {
                $model->syncSeoScore();
            }
        });

        RankForgeMeta::saved(static function (RankForgeMeta $meta): void {
            $owner = $meta->model;

            if ($owner instanceof Model && method_exists($owner, 'syncSeoScore')) {
                $owner->setRelation('rankforgeMeta', $meta);
                $owner->syncSeoScore();
            }
        });
    }
}
