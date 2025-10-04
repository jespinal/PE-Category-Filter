<?php

namespace PavelEspinal\WpPlugins\PECategoryFilter\Tests\Unit\Core;

use PavelEspinal\WpPlugins\PECategoryFilter\Core\Container;
use PHPUnit\Framework\TestCase;

/**
 * Container Test
 *
 * @package PE Category Filter
 * @since 2.0.0
 */
class ContainerTest extends TestCase
{
    /**
     * Container instance
     */
    private Container $container;

    /**
     * Set up test
     */
    protected function setUp(): void
    {
        $this->container = new Container();
    }

    /**
     * Test container bind method
     */
    public function testBind(): void
    {
        $this->container->bind('test', 'TestClass');
        
        $this->assertTrue($this->container->has('test'));
    }

    /**
     * Test container singleton method
     */
    public function testSingleton(): void
    {
        $this->container->singleton('test', 'TestClass');
        
        $this->assertTrue($this->container->has('test'));
    }

    /**
     * Test container make method with class string
     */
    public function testMakeWithClassString(): void
    {
        $this->container->bind('test', TestClass::class);
        
        $instance = $this->container->make('test');
        
        $this->assertInstanceOf(TestClass::class, $instance);
    }

    /**
     * Test container make method with callable
     */
    public function testMakeWithCallable(): void
    {
        $this->container->bind('test', function () {
            return new TestClass();
        });
        
        $instance = $this->container->make('test');
        
        $this->assertInstanceOf(TestClass::class, $instance);
    }

    /**
     * Test container make method with dependency injection
     */
    public function testMakeWithDependencyInjection(): void
    {
        $this->container->bind('dependency', TestDependency::class);
        $this->container->bind('test', function (Container $container) {
            $dependency = $container->make('dependency');
            return new TestClassWithDependency($dependency);
        });
        
        $instance = $this->container->make('test');
        
        $this->assertInstanceOf(TestClassWithDependency::class, $instance);
    }

    /**
     * Test container singleton behavior
     */
    public function testSingletonBehavior(): void
    {
        $this->container->singleton('test', function () {
            return new TestClass();
        });
        
        $instance1 = $this->container->make('test');
        $instance2 = $this->container->make('test');
        
        $this->assertSame($instance1, $instance2);
    }

    /**
     * Test container make method with non-singleton
     */
    public function testMakeNonSingleton(): void
    {
        $this->container->bind('test', function () {
            return new TestClass();
        });
        
        $instance1 = $this->container->make('test');
        $instance2 = $this->container->make('test');
        
        $this->assertNotSame($instance1, $instance2);
    }

    /**
     * Test container make method with non-existent service
     */
    public function testMakeNonExistentService(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Service 'non-existent' is not registered in the container.");
        
        $this->container->make('non-existent');
    }

    /**
     * Test container has method
     */
    public function testHas(): void
    {
        $this->assertFalse($this->container->has('test'));
        
        $this->container->bind('test', 'TestClass');
        
        $this->assertTrue($this->container->has('test'));
    }

    /**
     * Test container getServices method
     */
    public function testGetServices(): void
    {
        $this->container->bind('test1', 'TestClass1');
        $this->container->bind('test2', 'TestClass2');
        
        $services = $this->container->getServices();
        
        $this->assertCount(2, $services);
        $this->assertArrayHasKey('test1', $services);
        $this->assertArrayHasKey('test2', $services);
    }

    /**
     * Test container clear method
     */
    public function testClear(): void
    {
        $this->container->singleton('test', function () {
            return new TestClass();
        });
        
        // Make sure instance is created
        $this->container->make('test');
        
        $this->container->clear();
        
        // Should create new instance after clear
        $instance1 = $this->container->make('test');
        $instance2 = $this->container->make('test');
        
        $this->assertSame($instance1, $instance2);
    }
}

/**
 * Test class for container tests
 */
class TestClass
{
    public function __construct()
    {
        // Simple test class
    }
}

/**
 * Test dependency class
 */
class TestDependency
{
    public function __construct()
    {
        // Simple dependency
    }
}

/**
 * Test class with dependency
 */
class TestClassWithDependency
{
    private TestDependency $dependency;

    public function __construct(TestDependency $dependency)
    {
        $this->dependency = $dependency;
    }

    public function getDependency(): TestDependency
    {
        return $this->dependency;
    }
}
