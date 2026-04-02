<?php

use PHPUnit\Framework\TestCase;

class ZendMemoryTest extends TestCase
{
    public function testFactoryWithNoneBackend(): void
    {
        $manager = Zend_Memory::factory('none');
        $this->assertInstanceOf(Zend_Memory_Manager::class, $manager);
    }

    public function testFactoryWithNoneCaseInsensitive(): void
    {
        $manager = Zend_Memory::factory('None');
        $this->assertInstanceOf(Zend_Memory_Manager::class, $manager);
    }

    public function testFactoryWithFileBackend(): void
    {
        $cacheDir = sys_get_temp_dir() . '/zend_memory_test_' . uniqid();
        mkdir($cacheDir);
        try {
            $manager = Zend_Memory::factory('File', ['cache_dir' => $cacheDir]);
            $this->assertInstanceOf(Zend_Memory_Manager::class, $manager);
        } finally {
            array_map('unlink', glob($cacheDir . '/*') ?: []);
            @rmdir($cacheDir);
        }
    }

    public function testFactoryThrowsOnInvalidBackend(): void
    {
        $this->expectException(Zend_Memory_Exception::class);
        Zend_Memory::factory('NonExistentBackend');
    }
}
