<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class SimpleTest extends TestCase
{
    /** @test */
    public function simple_test()
    {
        $this->assertEquals(5, 2+3, 'Five was equal to 2+3');
        $this->assertTrue(true);
    }
}