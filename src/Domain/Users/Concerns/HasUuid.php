<?php

declare(strict_types=1);

namespace Domain\Users\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

///**
// * @template TModelClass
// */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(
//            /** @param TModelClass $model */
            static fn (Model $model) =>
            $model->uuid = Str::uuid()->toString()
        );
    }
}
