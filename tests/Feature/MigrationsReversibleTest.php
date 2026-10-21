<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

/**
 * TestCase::setUp() already ran `migrate`; rolling back and re-migrating
 * proves every down() actually drops what its up() created.
 */
it('rolls back every package migration cleanly and can re-migrate afterward', function (): void {
    expect(Schema::hasTable('rankforge_meta'))->toBeTrue();

    $this->artisan('migrate:rollback')->assertSuccessful();

    expect(Schema::hasTable('rankforge_meta'))->toBeFalse();

    $this->artisan('migrate')->assertSuccessful();

    expect(Schema::hasTable('rankforge_meta'))->toBeTrue()
        ->and(Schema::hasColumn('rankforge_meta', 'meta_title_translations'))->toBeTrue();
});
