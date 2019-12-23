<?php

namespace EasyDoc\Tests;

use FilesystemIterator;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class TestCase extends PHPUnitTestCase
{
    protected $tempDirectory;

    protected function setUp()
    {
        $this->tempDirectory = sys_get_temp_dir().'/doc-'.mt_rand(0, 9999999);
    }

    protected function tearDown()
    {
        if (file_exists($this->tempDirectory)) {
            $it = new RecursiveDirectoryIterator($this->tempDirectory, FilesystemIterator::SKIP_DOTS);
            $it = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($it as $file) {
                if ($file->isDir()) {
                    rmdir($file->getPathname());

                    continue;
                }

                unlink($file->getPathname());
            }

            rmdir($this->tempDirectory);
        }
    }

    protected function getFileContents(string $path): ?string
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            return null;
        }

        return str_replace("\r\n", "\n", $contents);
    }

    protected function getPathImage(string $path = null)
    {
        $path = $path ?: $this->tempDirectory;

        if (is_file($path)) {
            return $this->getFileContents($path);
        }

        $image = [];

        foreach (scandir($path) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $image[$item] = $this->getPathImage("$path/$item");
        }

        return $image;
    }

    protected function assertDirectoryImage(array $expectedDirectoryImage, string $message = '')
    {
        return $this->assertSame($expectedDirectoryImage, $this->getPathImage(), $message);
    }
}