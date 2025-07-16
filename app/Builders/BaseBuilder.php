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
     * @template T of BaseBuilder
     * @param BaseBuilder<TModelClass> $query
     * @param class-string<T> $builderClass
     * @return T
     * @throws \Exception
     */
    public function getBuilder(BaseBuilder $query, string $builderClass): BaseBuilder
    {
        if (!$query instanceof $builderClass) {
            throw new \Exception('Query is not a ' . $builderClass);
        }
        return $query;
    }
} 