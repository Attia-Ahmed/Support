<?php

namespace Attia\Support;

use ReflectionClass;
use ReflectionMethod;
use Illuminate\Testing\TestResponse;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;
use Attia\Support\Database\QueryBuilderMixin;
use Attia\Support\Testing\TestResponseViewMixin;

class SupportBaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        if (class_exists(TestResponse::class)) {
            $this->registerMixin(TestResponse::class, TestResponseViewMixin::class);
        }
        $this->registerMixin(Builder::class, QueryBuilderMixin::class);
    }

    protected function registerMixin(string $target, $mixin)
    {
        $reflectionClass = new ReflectionClass($mixin);
        $methods = ($reflectionClass)->getMethods(
            ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
        );

        foreach ($methods as $method) {
            $target::macro($method->name, function (...$args) use ($mixin, $method) {
                $mixinClass = new $mixin($this);
                return [$mixinClass, $method->name](...$args);
            }
            );
        }

    }
    public function boot()
    {
    }
}
