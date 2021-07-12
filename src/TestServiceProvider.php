<?php

namespace Amims71\AutoTest;

use Amims71\AutoTest\Console\GenerateUnitTesting;
use Amims71\AutoTest\Console\UnitTestHelper;
use Amims71\AutoTest\Console\UnitTestHelperJson;
use Illuminate\Support\ServiceProvider;

class TestServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            GenerateUnitTesting::class,
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([UnitTestHelperJson::class,UnitTestHelper::class]);
    }
}
