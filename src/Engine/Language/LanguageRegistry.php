<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Engine\Language;

use AmjadIqbal\FilamentRankForge\Engine\Language\Packs\ArabicPack;
use AmjadIqbal\FilamentRankForge\Engine\Language\Packs\EnglishPack;
use AmjadIqbal\FilamentRankForge\Engine\Language\Packs\FrenchPack;
use AmjadIqbal\FilamentRankForge\Engine\Language\Packs\GermanPack;
use AmjadIqbal\FilamentRankForge\Engine\Language\Packs\SpanishPack;
use AmjadIqbal\FilamentRankForge\Engine\Language\Packs\UrduPack;

final class LanguageRegistry
{
    /**
     * @var array<string, LanguagePack>
     */
    private array $packs = [];

    /**
     * @param  LanguagePack[]  $packs
     */
    public function __construct(array $packs = [])
    {
        foreach ($packs === [] ? self::defaultPacks() : $packs as $pack) {
            $this->register($pack);
        }
    }

    /**
     * @return LanguagePack[]
     */
    public static function defaultPacks(): array
    {
        return [
            new EnglishPack,
            new SpanishPack,
            new FrenchPack,
            new GermanPack,
            new ArabicPack,
            new UrduPack,
        ];
    }

    public function register(LanguagePack $pack): void
    {
        $this->packs[$pack->locale()] = $pack;
    }

    public function has(string $locale): bool
    {
        return isset($this->packs[$locale]);
    }

    public function get(string $locale): LanguagePack
    {
        return $this->packs[$locale] ?? $this->packs['en'] ?? new EnglishPack;
    }
}
