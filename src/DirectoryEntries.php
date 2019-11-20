<?php

namespace Liborm85\ComposerVendorCleaner;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class DirectoryEntries
{
    /**
     * @var string;
     */
    private $path;

    /**
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @return array
     */
    public function getEntries()
    {
        $entries = [];
        $directory = new RecursiveDirectoryIterator($this->path, FilesystemIterator::UNIX_PATHS);
        /** @var $iterator RecursiveDirectoryIterator */
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

        return $entries;
    }

}
