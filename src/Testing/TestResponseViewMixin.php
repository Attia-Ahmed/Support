<?php

namespace Attia\Support\Testing;

use Countable;
use Illuminate\Testing\Assert;
use Illuminate\Testing\TestResponse;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Testing\AssertableJsonString;

final class TestResponseViewMixin
{
    public function __construct(protected TestResponse $response)
    {
    }

    public function assertViewCount(string $key, int $count)
    {
        return $this->response->assertViewHas($key, function (Countable|array|null $data) use ($count) {
            $data ??= [];
            $actual_count = is_array($data) ? count($data) : $data->count();
            TestCase::assertEquals($count, $actual_count);

            return true;
        });
    }

    public function assertViewData(string $key, array $data)
    {
        return $this->response->assertViewHas($key, function (Arrayable|array|null $actual_data) use ($data) {
            $actual_data ??= [];
            $actual_data = $actual_data instanceof Arrayable ? $actual_data->toArray() : $actual_data;
            Assert::assertArraySubset($data, $actual_data);

            return true;
        });
    }

    public function assertViewStructrue(string $key, array $data)
    {
        return $this->response->assertViewHas($key, function (Arrayable|array|null $actual_data) use ($data) {
            $actual_data ??= [];
            $actual_data = $actual_data instanceof Arrayable ? $actual_data->toArray() : $actual_data;

            (new AssertableJsonString($actual_data))->assertStructure($data);

            return true;
        });
    }
}