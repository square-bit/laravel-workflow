<?php

namespace Squarebit\Workflows\Helpers;

use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class ModelHelper
{
    /**
     * @param  int|array<int, int>|array<int, Model>|Model|Collection<int, int>|Collection<int, Model>  $ids
     * @return array<int, int|string>
     */
    public static function toIdsArray(int|array|Model|Collection $ids): array
    {
        $ids = collect(Arr::wrap($ids));

        return $ids[0] instanceof Model
            ? $ids->map->getKey()->toArray()
            : $ids->toArray();
    }
}
