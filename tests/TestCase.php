<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Spatie caches its permission registry; reset it per test so grants
        // always resolve from the current database (avoids cross-test bleed).
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
