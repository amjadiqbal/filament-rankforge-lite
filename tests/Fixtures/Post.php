<?php

declare(strict_types=1);

namespace AmjadIqbal\FilamentRankForge\Tests\Fixtures;

use AmjadIqbal\FilamentRankForge\Concerns\HasSeo;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasSeo;

    protected $guarded = [];
}
