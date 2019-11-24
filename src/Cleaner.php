<?php

namespace Liborm85\ComposerVendorCleaner;

use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use FilesystemIterator;
use RecursiveDirectoryIterator;

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
     * @var Package[]
     */
    private $packages;

    /**
     * @var bool
     */
    private $matchCase;

    /**
     * @param IOInterface $io
     * @param Filesystem $filesystem
     * @param string $vendorDir
     * @param Package[] $packages
     * @param bool $matchCase
     */
    public function __construct($io, $filesystem, $vendorDir, $packages, $matchCase)
    {
        $this->io = $io;
        $this->filesystem = $filesystem;
        $this->vendorDir = $vendorDir;
        $this->packages = $packages;
        $this->matchCase = $matchCase;
    }

    /**
     * @param array $devFiles
     */
    public function cleanup($devFiles)
    {
        $this->io->write("");
        $this->io->write("Composer vendor cleaner: <info>Cleaning vendor directory</info>");

        $directory = new Directory();
        $directory->addPath($this->vendorDir);
        $allFiles = $directory->getEntries();

        $globPatterns = $this->buildGlobPatternFromDevFiles($devFiles);
        foreach ($globPatterns as $globPattern) {
            $this->io->write(
                "Composer vendor cleaner: Found pattern '<info>{$globPattern}</info>' for remove development files",
                true,
                IOInterface::DEBUG
            );
        }

        $globFilter = new GlobFilter();
        foreach ($globPatterns as $globPattern) {
            $globFilter->addInclude($globPattern, $this->matchCase);
        }

        $filesToRemove = $globFilter->getFilteredEntries($allFiles);

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
     * @param string $directory
     * @return bool
     */
    private function isEmptyDirectory($directory)
    {
        $iterator = new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS);

        return iterator_count($iterator) === 0;
    }
}
