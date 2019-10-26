<?php

namespace Liborm85\ComposerVendorCleaner;

class Cleaner
{
    /**
     * @var string
     */
    private $vendorDir;

    /**
     * @param string $vendorDir
     */
    public function __construct($vendorDir)
    {
        $this->vendorDir = $vendorDir;
    }

    /**
     * @param array $devFiles
     */
    public function cleanup($devFiles) {
        $files = $this->getAllFiles($this->vendorDir);
    }

    /**
     * @param string $vendorDir
     * @return array
     */
    private function getAllFiles($vendorDir)
    {
        $files = [];
        $directory = new \RecursiveDirectoryIterator($vendorDir, \FilesystemIterator::UNIX_PATHS);
        $iterator = new \RecursiveIteratorIterator($directory);
        foreach ($iterator as $file) {
            $fileSubPath = $iterator->getSubPathname();
            if ((substr($fileSubPath, -3) === '/..') || ($fileSubPath === '..') || ($fileSubPath === '.')) {
                continue;
            } elseif (substr($fileSubPath, -2) === '/.') {
                $fileSubPath = rtrim($fileSubPath, '.');
            }

            $files[] = '/' . $fileSubPath;
        }

        return $files;
    }
}
