<?php

namespace Liborm85\ComposerVendorCleaner;

use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use FilesystemIterator;
use Liborm85\ComposerVendorCleaner\Finder\Glob;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

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
     * @var bool
     */
    private $matchCase;

    /**
     * @param IOInterface $io
     * @param Filesystem $filesystem
     * @param string $vendorDir
     * @param bool $matchCase
     */
    public function __construct($io, $filesystem, $vendorDir, $matchCase)
    {
        $this->io = $io;
        $this->filesystem = $filesystem;
        $this->vendorDir = $vendorDir;
        $this->matchCase = $matchCase;
    }

    /**
     * @param array $devFiles
     */
    public function cleanup($devFiles)
    {
        $this->io->write("");
        $this->io->write("Composer vendor cleaner: <info>Cleaning vendor directory</info>");

        $allFiles = $this->getAllFiles($this->vendorDir);

        $globPatterns = $this->buildGlobPatternFromDevFiles($devFiles);
        foreach ($globPatterns as $globPattern) {
            $this->io->write(
                "Composer vendor cleaner: Found pattern '<info>{$globPattern}</info>' for remove development files",
                true,
                IOInterface::DEBUG
            );
        }

        $regexPatterns = $this->globPatternsToRegexPatterns($globPatterns, $this->matchCase);

        $filesToRemove = [];
        foreach ($regexPatterns as $regexPattern) {
            $filesToRemove += preg_grep($regexPattern, $allFiles);
        }

        $filesToRemove = array_unique($filesToRemove);

        krsort($filesToRemove);

        $this->removeFiles($filesToRemove);
    }

    /**
     * @param array $filesToRemove
     */
    private function removeFiles($filesToRemove)
    {
        $removedDirectories = 0;
        $removedFiles = 0;
        foreach ($filesToRemove as $fileToRemove) {
            $filepath = $this->vendorDir . $fileToRemove;
            if (is_dir($filepath)) {
                if (!$this->isEmptyDirectory($filepath)) {
                    $this->io->write(
                        "Composer vendor cleaner: Directory '<info>{$fileToRemove}</info>' not removed, because isn't empty",
                        true,
                        IOInterface::VERBOSE
                    );
                    continue;
                }

                $this->filesystem->removeDirectory($filepath);

                $this->io->write(
                    "Composer vendor cleaner: Directory '<info>{$fileToRemove}</info>' removed",
                    true,
                    IOInterface::VERBOSE
                );
                $removedDirectories++;
            } else {
                $this->filesystem->remove($filepath);

                $removedFiles++;
                $this->io->write(
                    "Composer vendor cleaner: File '<info>{$fileToRemove}</info>' removed",
                    true,
                    IOInterface::VERBOSE
                );
            }
        }

        $this->io->write(
            "Composer vendor cleaner: <info>Removed {$removedFiles} files and {$removedDirectories} directories</info>"
        );
    }

    /**
     * @param array $globPatterns
     * @param bool $matchCase
     * @return array
     */
    private function globPatternsToRegexPatterns($globPatterns, $matchCase)
    {
        $regexPatterns = [];
        foreach ($globPatterns as $globPattern) {
            $regexPattern = Glob::toRegex($globPattern, false);
            if (!$matchCase) {
                $regexPattern .= 'i';
            }

            $regexPatterns[] = $regexPattern;
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
                $filePatternSuffix = '';
                if (substr($devFile, 0, 1) !== '/') {
                    $filePatternPrefix = '/**/';
                }

                if (substr($devFile, -1) === '/') {
                    $filePatternSuffix = '**';
                }

                $globPattern = '/' . ltrim($directoryPattern . $filePatternPrefix . $devFile . $filePatternSuffix, '/');

                $globPatterns[] = $globPattern;
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
        $directory = new RecursiveDirectoryIterator($vendorDir, FilesystemIterator::UNIX_PATHS);
        /** @var $iterator RecursiveDirectoryIterator */
        $iterator = new RecursiveIteratorIterator($directory);

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

    /**
     * @param string $directory
     * @return bool
     */
    private function isEmptyDirectory($directory)
    {
        $iterator = new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS);

        return iterator_count($iterator) === 0;
    }
}
