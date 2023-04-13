<?php

namespace SethPhat\EloquentDocs\Tests\Fixtures\BulkFixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailFixture extends Model
{
    protected $table = 'emails';

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserFixture::class);
    }
}