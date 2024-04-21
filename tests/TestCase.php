<?php

namespace Attia\Support\Tests;

use Illuminate\Foundation\Application;
use Attia\Support\SupportBaseServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        (new SupportBaseServiceProvider(new Application()))->register();
    }
}
