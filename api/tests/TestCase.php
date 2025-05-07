<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates a mock object for the specified class.
     *
     * @param string $class
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    protected function mock(string $class)
    {
        return $this->createMock($class);
    }
} 