<?php

declare(strict_types=1);

/*
 * House rule: no brown anywhere in the UI, including tan, maroon, sienna,
 * chocolate and dark or muted orange/red. Warm colours are allowed only as
 * solid text, strokes and dots; translucent warm fills turn brown on dark
 * backgrounds.
 */
function shippedUiFiles(): array
{
    $root = dirname(__DIR__, 2);
    $files = [];

    foreach (['resources/views', 'src'] as $dir) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("{$root}/{$dir}", FilesystemIterator::SKIP_DOTS));

        foreach ($iterator as $file) {
            if (preg_match('/\.(php|css|js)$/', $file->getFilename())) {
                $files[] = $file->getPathname();
            }
        }
    }

    return $files;
}

it('uses no brown colour names or brown-family Tailwind classes', function (string $path): void {
    $source = strtolower((string) file_get_contents($path));

    expect($source)->not->toMatch('/\b(brown|tan|maroon|sienna|chocolate|saddlebrown|peru|burlywood|rosybrown)\b(?![-\w])/')
        ->and($source)->not->toMatch('/\b(?:text|bg|border|stroke|fill|ring)-stone-\d+/')
        ->and($source)->not->toMatch('/\b(?:text|bg|border|stroke|fill|ring)-(?:amber|orange|yellow)-(?:7|8|9)\d\d\b/')
        ->and($source)->not->toMatch('/\b(?:text|bg|border|stroke|fill|ring)-red-9\d\d\b/');
})->with(fn (): array => shippedUiFiles());

it('never uses a translucent warm fill', function (string $path): void {
    $source = strtolower((string) file_get_contents($path));

    // Translucent amber/orange/red backgrounds: Tailwind opacity modifiers and rgba() fills.
    expect($source)->not->toMatch('/\bbg-(?:amber|orange|yellow|red|warning|danger)-\d+\/\d+/')
        ->and($source)->not->toMatch('/background(?:-color)?\s*:\s*rgba?\(\s*(?:2[0-5]\d|1[5-9]\d)\s*,?\s*(?:\d{1,3})\s*,?\s*(?:[0-9]|[1-9]\d)\s*[,\/]\s*0?\.\d+/');
})->with(fn (): array => shippedUiFiles());
