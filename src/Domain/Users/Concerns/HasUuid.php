<?php

declare(strict_types=1);

namespace Domain\Users\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Ramsey\Uuid\UuidInterface;

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

    public static function findByUuid(UuidInterface $uuid): self
    {
        return static::query()->where('uuid', $uuid->toString())->sole();
    }
}
