<?php

namespace SethPhat\EloquentDocs\Tests\Fixtures;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UserFixture extends Model
{
    protected $table = 'users';

    protected $casts = [
        'payload' => 'array',
        'additional_payload' => 'object',
        'external_data' => 'collection',
    ];

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

    public function getLastEmailAttribute(): EmailFixture
    {
        return new EmailFixture();
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
            set: fn ($value) => $this->value = $value,
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