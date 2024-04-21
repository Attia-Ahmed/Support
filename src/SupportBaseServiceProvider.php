<?php

namespace Attia\Support;

use ReflectionClass;
use ReflectionMethod;
use Illuminate\Testing\TestResponse;
use Illuminate\Support\ServiceProvider;
use Attia\Support\Testing\TestResponseViewMixin;

class SupportBaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        if (class_exists(TestResponse::class)) {
            $this->registerMixin(TestResponse::class, TestResponseViewMixin::class);
        }
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
