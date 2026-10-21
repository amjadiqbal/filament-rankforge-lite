<?php

declare(strict_types=1);

use AmjadIqbal\FilamentRankForge\Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| tests/Unit/Engine covers src/Engine/, which is framework-free by rule
| (see CLAUDE.md), so those tests run against plain PHPUnit\Framework\TestCase
| — no Testbench binding here. tests/Feature covers the Laravel/Filament
| integration layer (HasSeo, migrations, Filament components) and binds
| the Testbench TestCase.
|
*/
uses(TestCase::class)->in('Feature');
