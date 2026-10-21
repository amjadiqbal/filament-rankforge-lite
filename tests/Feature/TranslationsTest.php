<?php

declare(strict_types=1);

/**
 * @return array<string, mixed>
 */
function loadRankforgeTranslations(string $locale): array
{
    return require __DIR__."/../../resources/lang/{$locale}/rankforge.php";
}

/**
 * @param  array<string, mixed>  $array
 * @return string[]
 */
function dottedKeys(array $array, string $prefix = ''): array
{
    $keys = [];

    foreach ($array as $key => $value) {
        $path = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

        if (is_array($value)) {
            $keys = [...$keys, ...dottedKeys($value, $path)];

            continue;
        }

        $keys[] = $path;
    }

    sort($keys);

    return $keys;
}

it('ships a rankforge.php translation file for all 6 supported locales', function (): void {
    foreach (['en', 'es', 'fr', 'de', 'ar', 'ur'] as $locale) {
        expect(is_file(__DIR__."/../../resources/lang/{$locale}/rankforge.php"))->toBeTrue("Missing translation file for [{$locale}]");
    }
});

it('has identical translation keys across every locale', function (string $locale): void {
    $englishKeys = dottedKeys(loadRankforgeTranslations('en'));
    $localeKeys = dottedKeys(loadRankforgeTranslations($locale));

    expect($localeKeys)->toBe($englishKeys);
})->with(['es', 'fr', 'de', 'ar', 'ur']);

it('has no empty translation values in any locale', function (string $locale): void {
    $keys = dottedKeys(loadRankforgeTranslations($locale));
    $translations = loadRankforgeTranslations($locale);

    foreach ($keys as $key) {
        $value = data_get($translations, $key);

        expect($value)->toBeString()
            ->and(trim((string) $value))->not->toBe('', "Empty translation for [{$key}] in [{$locale}]");
    }
})->with(['en', 'es', 'fr', 'de', 'ar', 'ur']);
