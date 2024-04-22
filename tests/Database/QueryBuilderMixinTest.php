<?php

namespace Attia\Support\Tests\Database;

use InvalidArgumentException;
use Attia\Support\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Grammars\SQLiteGrammar;

class QueryBuilderMixinTest extends TestCase
{
    public function testWhereNullOr()
    {
        $builder = $this->getBuilder();
        $query = $builder->whereOrNull('name', 'test');
        $expected = 'select * from "table" where ("name" = ? or "name" is null)';
        $this->assertEquals($expected, $query->toSql());
    }

    public function testWhereNullOrSimpleWithOperator()
    {
        $builder = $this->getBuilder();
        $query = $builder->whereOrNull('name', 'like', '%test%');
        $expected = 'select * from "table" where ("name" like ? or "name" is null)';
        $this->assertEquals($expected, $query->toSql());
    }

    public function testWhereNullOrArray()
    {
        $builder = $this->getBuilder();
        $query = $builder->whereOrNull(['name', 'name2'], 'test');
        $expected = 'select * from "table" where ("name" = ? or "name" is null) and ("name2" = ? or "name2" is null)';
        $this->assertEquals($expected, $query->toSql());
    }

    public function testWhereNullOrArrayWithOperator()
    {
        $builder = $this->getBuilder();
        $query = $builder->whereOrNull(['name', 'name2'], '>=', 'test');
        $expected = 'select * from "table" where ("name" >= ? or "name" is null) and ("name2" >= ? or "name2" is null)';
        $this->assertEquals($expected, $query->toSql());
    }

    public function testWhereNullOrExpression()
    {
        $builder = $this->getBuilder();
        $query = $builder->whereOrNull(new Expression('GREATEST(from, to)'), '>', 2);
        $expected = 'select * from "table" where (GREATEST(from, to) > ? or GREATEST(from, to) is null)';
        $this->assertEquals($expected, $query->toSql());
        $this->assertEquals([2], $query->getBindings());
    }

    public function testWhereNullOrCallback()
    {
        $builder = $this->getBuilder();
        $query = $builder->whereOrNull('first_name', function ($query) {
            $query->where('last_name', 'test');
        });
        $expected = 'select * from "table" where (("last_name" = ?) or "first_name" is null)';
        $this->assertEquals($expected, $query->toSql());
        $this->assertEquals(['test'], $query->getBindings());
    }

    public function testWhereNullOrCallbackWithArray()
    {
        $builder = $this->getBuilder();
        $query = $builder->whereOrNull(['first_name', 'last_name'], function ($query) {
            $query->where('name', 'test');
        });
        $expected = 'select * from "table" where (("name" = ?) or "first_name" is null or "last_name" is null)';
        $this->assertEquals($expected, $query->toSql());
        $this->assertEquals(['test'], $query->getBindings());
    }

    public function testWhereNullOrCallbackInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A value is prohibited when subquery is used.');

        $builder = $this->getBuilder();
        $builder->whereOrNull('first_name', function ($query) {
            $query->where('last_name', 'test');
        }, 'test');
    }

    public function testOrderByValues()
    {
        $builder = $this->getBuilder();
        $builder->orderByValues('first_name', [3, null, 1]);
        $this->assertEquals(
            'select * from "table" order by first_name = ? desc, first_name is ? desc, first_name = ? desc',
            $builder->toSql());
        $this->assertEquals([3, null, 1], $builder->getBindings());
    }

    private function getBuilder(): Builder
    {

        $builder = (new Builder(DB::connection(),new SQLiteGrammar()))
            ->select('*')->from('table');
        return $builder;
    }
}