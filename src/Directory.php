<?php

namespace Liborm85\ComposerVendorCleaner;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Directory
{
    /**
     * @var array;
     */
    private $paths = [];

    /**
     * @param string $path
     */
    public function addPath($path)
    {
        $this->paths[] = $path;
    }

    /**
     * @return array
     */
    public function getEntries()
    {
        $entries = [];
        foreach ($this->paths as $path) {
            $directory = new RecursiveDirectoryIterator($path, FilesystemIterator::UNIX_PATHS);
            /** @var RecursiveDirectoryIterator $iterator */
            $iterator = new RecursiveIteratorIterator($directory);

            foreach ($iterator as $file) {
                $fileSubPath = $iterator->getSubPathname();
                if ((substr($fileSubPath, -3) === '/..') || ($fileSubPath === '..') || ($fileSubPath === '.')) {
                    continue;
                } elseif (substr($fileSubPath, -2) === '/.') {
                    $fileSubPath = rtrim($fileSubPath, '.');
                }

                $entries[] = '/' . $fileSubPath;
            }
        }

        return $entries;
    }

    /**
     * @return array
     */
    public function getDirectories()
    {
        $entries = [];
        foreach ($this->paths as $path) {
            $directory = new RecursiveDirectoryIterator($path, FilesystemIterator::UNIX_PATHS);
            /** @var RecursiveDirectoryIterator $iterator */
            $iterator = new RecursiveIteratorIterator($directory);

            foreach ($iterator as $file) {
                $fileSubPath = $iterator->getSubPathname();
                if (substr($fileSubPath, -2) === '/.') {
                    $fileSubPath = rtrim($fileSubPath, '.');
                    $entries[] = '/' . $fileSubPath;
                }
            }
        }

        return $entries;
    }

}
