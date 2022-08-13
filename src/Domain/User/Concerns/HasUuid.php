<?php

namespace Domain\User\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(static fn(Model $model) =>
            $model->uuid = Str::uuid()->toString()
        );
    }
}
