<?php

namespace Attia\Support\Database;

use Closure;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;

final class QueryBuilderMixin
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

    public function orderByExpression(string|Expression $column, array $bindings = [], string $direction = 'asc')
    {
        $builder = $this->builder;
        if (is_string($column)) {
            $column = new Expression('('.$column.')');
        }
        $builder->addBinding($bindings, $builder->unions ? 'unionOrder' : 'order');

        $direction = strtolower($direction);

        if (!in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException('Order direction must be "asc" or "desc".');
        }

        $builder->{$builder->unions ? 'unionOrders' : 'orders'}[] = [
            'column'    => $column,
            'direction' => $direction,
        ];

        return $builder;
    }

    public function orderByValue(string|Expression $column, mixed $value = null, $direction = 'desc')
    {
        $operator = is_null($value) ? 'is' : '=';
        if ($column instanceof Expression) {
            $column = $column->getValue($this->builder->getGrammar());
        }
        return $this->builder->orderByExpression(new Expression($column.' '.$operator.' ?'), [$value], $direction);
    }

    public function nullsFirst(string|Expression $column)
    {
        return $this->builder->orderByValue($column, null, 'desc');
    }

    public function nullsLast(string|Expression $column)
    {
        return $this->builder->orderByValue($column, null, 'asc');
    }

    public function orderByValues(string|Expression $column, $values)
    {
        foreach ($values as $value) {
            $this->builder->orderByValue($column, $value);
        }
        return $this->builder;
    }
}