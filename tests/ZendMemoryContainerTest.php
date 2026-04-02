<?php

use PHPUnit\Framework\TestCase;

class ZendMemoryContainerTest extends TestCase
{
    public function testLockedContainerValue(): void
    {
        $container = new Zend_Memory_Container_Locked('test');
        $this->assertSame('test', $container->value);
    }

    public function testLockedContainerIsAlwaysLocked(): void
    {
        $container = new Zend_Memory_Container_Locked('test');
        $this->assertTrue($container->isLocked());
        $container->unlock();
        $this->assertTrue($container->isLocked());
    }

    public function testLockedContainerGetRef(): void
    {
        $container = new Zend_Memory_Container_Locked('ref');
        $ref = &$container->getRef();
        $this->assertSame('ref', $ref);
        $ref = 'modified';
        $this->assertSame('modified', $container->value);
    }

    public function testLockedContainerTouch(): void
    {
        $container = new Zend_Memory_Container_Locked('test');
        $container->touch();
        $this->assertSame('test', $container->value);
    }

    public function testLockedContainerLock(): void
    {
        $container = new Zend_Memory_Container_Locked('test');
        $container->lock();
        $this->assertTrue($container->isLocked());
    }

    public function testLockedContainerDestroy(): void
    {
        $container = new Zend_Memory_Container_Locked('test');
        $container->destroy();
        $this->assertSame('test', $container->value);
    }

    public function testValueObjectArrayAccess(): void
    {
        $backend = new Zend_Cache_Backend_BlackHole();
        $manager = new Zend_Memory_Manager($backend);
        $container = $manager->create('hello');

        $value = $container->value;
        $this->assertSame('h', $value[0]);
        $this->assertSame('e', $value[1]);
        $this->assertTrue(isset($value[0]));
        $this->assertFalse(isset($value[10]));
    }

    public function testValueObjectToString(): void
    {
        $backend = new Zend_Cache_Backend_BlackHole();
        $manager = new Zend_Memory_Manager($backend);
        $container = $manager->create('hello');
        $this->assertSame('hello', (string) $container->value);
    }

    public function testMovableContainerThrowsOnUnknownProperty(): void
    {
        $backend = new Zend_Cache_Backend_BlackHole();
        $manager = new Zend_Memory_Manager($backend);
        $container = $manager->create('test');
        $this->expectException(Zend_Memory_Exception::class);
        $_ = $container->nonexistent;
    }

    public function testMovableContainerThrowsOnSetUnknownProperty(): void
    {
        $backend = new Zend_Cache_Backend_BlackHole();
        $manager = new Zend_Memory_Manager($backend);
        $container = $manager->create('test');
        $this->expectException(Zend_Memory_Exception::class);
        $container->nonexistent = 'value';
    }
}
