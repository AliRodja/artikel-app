<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // @vite() di layout tidak butuh dev server / hasil build saat test.
        $this->withoutVite();
    }
}
