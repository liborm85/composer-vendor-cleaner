<?php

namespace Liborm85\ComposerVendorCleaner;

use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use Symfony\Component\Finder\Glob;

class Cleaner
{

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var string
     */
    private $vendorDir;

    /**
     * @param IOInterface $io
     * @param Filesystem $filesystem
     * @param string $vendorDir
     */
    public function __construct($io, $filesystem, $vendorDir)
    {
        $this->io = $io;
        $this->filesystem = $filesystem;
        $this->vendorDir = $vendorDir;
    }

    /**
     * @param array $devFiles
     */
    public function cleanup($devFiles)
    {
        $files = $this->getAllFiles($this->vendorDir);

        $globPatterns = $this->buildGlobPatternFromDevFiles($devFiles);
        foreach ($globPatterns as $globPattern) {
            $this->io->write("Found pattern '{$globPattern}'", true, IOInterface::VERBOSE);
        }

        $regexPatterns = $this->globPatternsToRegexPatterns($globPatterns);
        var_dump($regexPatterns);
    }

    private function globPatternsToRegexPatterns($globPatterns)
    {
        $regexPatterns = [];
        foreach ($globPatterns as $globPattern) {
            $regexPatterns[] = Glob::toRegex($globPattern);
        }

        return $regexPatterns;
    }

    /**
     * @param array $devFiles
     * @return array
     */
    private function buildGlobPatternFromDevFiles($devFiles)
    {
        $globPatterns = [];
        foreach ($devFiles as $devFileDirectory => $devFileDirectoryFiles) {
            $directoryPattern = rtrim($devFileDirectory, '/');
            foreach ($devFileDirectoryFiles as $devFile) {
                $filePatternPrefix = '';
                if (substr($devFile, 0, 1) !== '/') {
                    $filePatternPrefix = '/**/';
                }

                $globPatterns[] = $directoryPattern . $filePatternPrefix . $devFile;
            }
        }

        return $globPatterns;
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
