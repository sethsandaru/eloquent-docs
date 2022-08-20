<?php

namespace SethPhat\EloquentDocs\Tests\Fixtures;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserFixture extends Model
{
    public function emails(): HasMany
    {
        return $this->hasMany(EmailFixture::class);
    }

    public function userDetail(): HasOne
    {
        return $this->hasOne(UserDetailFixture::class);
    }

    public function getFullNameAttribute(): string
    {
        return '';
    }

    public function getIsAdminAttribute(): string
    {
        return '';
    }

    public function getUserTypeAttribute(): string
    {
        return '';
    }

    public function getTotalSalaryAttribute(): int
    {
        return 0;
    }

    public function getLevelsAttribute()
    {
        return 0;
    }

    /**
     * Get the user's first name.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst($value),
        );
    }

    /**
     * Get the user's first name.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst($value),
        );
    }
}