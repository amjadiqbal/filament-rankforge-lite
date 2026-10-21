<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Filament\Tables;

use AmjadIqbal\FilamentRankForge\Models\RankForgeMeta;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A sortable score column for any resource whose model uses HasSeo. Rendered
 * as solid coloured text rather than a badge: Filament's badges use a
 * translucent fill that turns the yellow mid-band muddy on dark backgrounds.
 * Eager-load the relation on the table query (->with('rankforgeMeta')) to
 * avoid one query per row.
 */
final class SeoScoreColumn
{
    /**
     * @param  string  $scoreColumn  Which rankforge_meta score column to read: score|seo_score|readability_score
     */
    public static function make(string $name = 'seo_score', string $scoreColumn = 'score'): TextColumn
    {
        return TextColumn::make($name)
            ->label(__('rankforge::rankforge.seo_score'))
            ->weight(FontWeight::Bold)
            ->state(static function (Model $record) use ($scoreColumn): ?int {
                $meta = $record->getAttribute('rankforgeMeta');

                return $meta instanceof RankForgeMeta ? $meta->{$scoreColumn} : null;
            })
            ->placeholder('—')
            ->color(static fn (?int $state): string => self::colorFor($state))
            ->sortable(query: static function (Builder $query, string $direction) use ($scoreColumn): Builder {
                $model = $query->getModel();

                return $query->orderBy(
                    RankForgeMeta::query()
                        ->select($scoreColumn)
                        ->whereColumn('model_id', $model->getQualifiedKeyName())
                        ->where('model_type', $model->getMorphClass())
                        ->limit(1),
                    $direction,
                );
            });
    }

    private static function colorFor(?int $score): string
    {
        if ($score === null) {
            return 'gray';
        }

        return match (true) {
            $score < (int) config('rankforge.score_thresholds.red_below', 40) => 'danger',
            $score >= (int) config('rankforge.score_thresholds.green_at_or_above', 70) => 'success',
            default => 'warning',
        };
    }
}
