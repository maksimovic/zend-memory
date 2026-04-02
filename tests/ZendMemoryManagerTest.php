<?php

use PHPUnit\Framework\TestCase;

class ZendMemoryManagerTest extends TestCase
{
    public function testConstructorWithoutBackend(): void
    {
        $manager = new Zend_Memory_Manager();
        $this->assertInstanceOf(Zend_Memory_Manager::class, $manager);
    }

    public function testConstructorWithBackend(): void
    {
        $backend = new Zend_Cache_Backend_BlackHole();
        $manager = new Zend_Memory_Manager($backend);
        $this->assertInstanceOf(Zend_Memory_Manager::class, $manager);
    }

    public function testGetSetMemoryLimit(): void
    {
        $manager = new Zend_Memory_Manager();
        $manager->setMemoryLimit(1048576);
        $this->assertSame(1048576, $manager->getMemoryLimit());
    }

    public function testDefaultMemoryLimitIsNegative(): void
    {
        $manager = new Zend_Memory_Manager();
        $this->assertSame(-1, $manager->getMemoryLimit());
    }

    public function testGetSetMinSize(): void
    {
        $manager = new Zend_Memory_Manager();
        $this->assertSame(16384, $manager->getMinSize());
        $manager->setMinSize(1024);
        $this->assertSame(1024, $manager->getMinSize());
    }

    public function testCreateLockedWithoutBackend(): void
    {
        $manager = new Zend_Memory_Manager();
        $container = $manager->createLocked('test value');
        $this->assertInstanceOf(Zend_Memory_Container_Locked::class, $container);
        $this->assertSame('test value', $container->value);
    }

    public function testCreateWithoutBackendReturnsLocked(): void
    {
        $manager = new Zend_Memory_Manager();
        $container = $manager->create('test value');
        // Without backend, all objects are locked
        $this->assertInstanceOf(Zend_Memory_Container_Locked::class, $container);
    }

    public function testCreateWithBackendReturnsAccessController(): void
    {
        $backend = new Zend_Cache_Backend_BlackHole();
        $manager = new Zend_Memory_Manager($backend);
        $container = $manager->create('test value');
        $this->assertInstanceOf(Zend_Memory_AccessController::class, $container);
    }

    public function testCreateLockedWithBackend(): void
    {
        $backend = new Zend_Cache_Backend_BlackHole();
        $manager = new Zend_Memory_Manager($backend);
        $container = $manager->createLocked('locked value');
        $this->assertInstanceOf(Zend_Memory_Container_Locked::class, $container);
        $this->assertSame('locked value', $container->value);
    }

    public function testMovableContainerGetValue(): void
    {
        $backend = new Zend_Cache_Backend_BlackHole();
        $manager = new Zend_Memory_Manager($backend);
        $container = $manager->create('hello');
        $this->assertSame('hello', (string) $container->value);
    }

    public function testMovableContainerSetValue(): void
    {
        $backend = new Zend_Cache_Backend_BlackHole();
        $manager = new Zend_Memory_Manager($backend);
        $container = $manager->create('hello');
        $container->value = 'world';
        $this->assertSame('world', (string) $container->value);
    }

    public function testMovableContainerGetRef(): void
    {
        $backend = new Zend_Cache_Backend_BlackHole();
        $manager = new Zend_Memory_Manager($backend);
        $container = $manager->create('ref test');
        $ref = &$container->getRef();
        $this->assertSame('ref test', $ref);
    }

    public function testMovableContainerTouch(): void
    {
        $backend = new Zend_Cache_Backend_BlackHole();
        $manager = new Zend_Memory_Manager($backend);
        $container = $manager->create('touch test');
        $container->touch();
        $this->assertSame('touch test', (string) $container->value);
    }

    public function testMovableContainerLockUnlock(): void
    {
        $backend = new Zend_Cache_Backend_BlackHole();
        $manager = new Zend_Memory_Manager($backend);
        $container = $manager->create('lock test');
        $this->assertFalse((bool) $container->isLocked());
        $container->lock();
        $this->assertTrue((bool) $container->isLocked());
        $container->unlock();
        $this->assertFalse((bool) $container->isLocked());
    }

    public function testMultipleContainers(): void
    {
        $backend = new Zend_Cache_Backend_BlackHole();
        $manager = new Zend_Memory_Manager($backend);
        $c1 = $manager->create('first');
        $c2 = $manager->create('second');
        $c3 = $manager->create('third');
        $this->assertSame('first', (string) $c1->value);
        $this->assertSame('second', (string) $c2->value);
        $this->assertSame('third', (string) $c3->value);
    }

    public function testSwapWithFileBackend(): void
    {
        $cacheDir = sys_get_temp_dir() . '/zend_memory_swap_' . uniqid();
        mkdir($cacheDir);
        try {
            $backend = new Zend_Cache_Backend_File(['cache_dir' => $cacheDir]);
            $manager = new Zend_Memory_Manager($backend);
            $manager->setMemoryLimit(1024);
            $manager->setMinSize(1);

            // Create objects that exceed memory limit to force swapping
            $containers = [];
            for ($i = 0; $i < 10; $i++) {
                $containers[] = $manager->create(str_repeat('x', 200));
            }

            // All values should still be accessible
            foreach ($containers as $i => $container) {
                $this->assertSame(str_repeat('x', 200), (string) $container->value);
            }
        } finally {
            unset($manager, $containers);
            array_map('unlink', glob($cacheDir . '/*') ?: []);
            @rmdir($cacheDir);
        }
    }
}
