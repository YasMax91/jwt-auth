<?php

use Orchestra\Testbench\TestCase;

uses(TestCase::class)->in('Feature');

function getPackageProviders($app)
{
    return [\RaDevs\ApiJsonResponse\ApiJsonResponseServiceProvider::class];
}
