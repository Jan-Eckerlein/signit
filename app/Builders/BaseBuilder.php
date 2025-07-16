<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;


/**
 * @template TModelClass of \Illuminate\Database\Eloquent\Model
 * @extends Builder<TModelClass>
 */
class BaseBuilder extends Builder
{
    /**
     * @template T of Builder
     * @param Builder $query
     * @param class-string<T> $builderClass
     * @return T
     * @throws \Exception
     */
    public function getBuilder(Builder $query, string $builderClass): Builder
    {
        if (!$query instanceof $builderClass) {
            throw new \Exception('Query is not a ' . $builderClass);
        }
        return $query;
    }
} 