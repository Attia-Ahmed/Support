<?php

namespace Attia\Support\Database;

use Closure;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Illuminate\Database\Query\Builder;

class QueryBuilderMixin
{
    public function __construct(protected Builder $builder)
    {
    }

    /**
     * Add a "where or null" clause to the query.
     *
     * @param  string|array|\Illuminate\Contracts\Database\Query\Expression  $columns
     * @param  \Closure|string  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return Builder
     */
    public function whereOrNull($columns, $operator = null, $value = null, $boolean = 'and', $not = false)
    {
        $builder = $this->builder;

        if ($operator instanceof Closure) {
            if (!is_null($value)) {
                throw new InvalidArgumentException('A value is prohibited when subquery is used.');
            }

            return $builder->whereNested(function (Builder $query) use ($not, $operator, $columns) {
                return $query->whereNested($operator)
                    ->whereNull($columns, 'or', $not);
            }, $boolean);
        }

        foreach (Arr::wrap($columns) as $column) {
            $builder->whereNested(function (Builder $query) use ($value, $not, $operator, $column) {
                $query->where($column, $operator, $value)
                    ->whereNull($column, 'or', $not);
            }, $boolean);
        }

        return $builder;
    }


}