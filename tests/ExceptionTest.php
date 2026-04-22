<?php

namespace Pig\Router\Tests;

use Pig\Router\NotFoundException;
use Pig\Router\InvalidCallbackException;
use Pig\Router\MethodNotFoundException;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function testNotFoundException()
    {
        $exception = new NotFoundException('404 Not Found');
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('404 Not Found', $exception->getMessage());
    }

    public function testInvalidCallbackException()
    {
        $exception = new InvalidCallbackException('Invalid callback provided');
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('Invalid callback provided', $exception->getMessage());
    }

    public function testMethodNotFoundException()
    {
        $exception = new MethodNotFoundException('Method not found');
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals('Method not found', $exception->getMessage());
    }

    public function testExceptionInheritance()
    {
        $notFound = new NotFoundException();
        $invalidCallback = new InvalidCallbackException();
        $methodNotFound = new MethodNotFoundException();

        $this->assertInstanceOf(\Exception::class, $notFound);
        $this->assertInstanceOf(\Exception::class, $invalidCallback);
        $this->assertInstanceOf(\Exception::class, $methodNotFound);
    }
}
