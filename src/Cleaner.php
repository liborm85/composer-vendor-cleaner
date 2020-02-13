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
     * @var int
     */
    private $removedDirectories = 0;

    /**
     * @var int
     */
    private $removedFiles = 0;

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

        foreach ($this->packages as $package) {
            $devFilesForPackage = $this->findGlobPatternsForPackage($package->getPrettyName(), $devFiles);
            if (empty($devFilesForPackage)) {
                continue;
            }

            $directory = new Directory();
            $directory->addPath($package->getInstallPath());
            $allFiles = $directory->getEntries();

            $globPatterns = $this->buildGlobPatternForFilter($devFilesForPackage);

            $globFilter = new GlobFilter();
            foreach ($globPatterns as $globPattern) {
                $globFilter->addInclude($globPattern, $this->matchCase);
            }

            $filesToRemove = $globFilter->getFilteredEntries($allFiles);

            rsort($filesToRemove);

            $this->removeFiles($package->getPrettyName(), $package->getInstallPath(), $filesToRemove);
        }

        $packagesCount = count($this->packages);

        $this->io->write(
            "Composer vendor cleaner: <info>Removed {$this->removedFiles} files and {$this->removedDirectories} directories from {$packagesCount} packages.</info>"
        );
    }

    /**
     * @param string $packageName
     * @param string $rootDir
     * @param array $filesToRemove
     */
    private function removeFiles($packageName, $rootDir, $filesToRemove)
    {
        $removedDirectories = 0;
        $removedFiles = 0;
        foreach ($filesToRemove as $fileToRemove) {
            $filepath = $rootDir . $fileToRemove;
            if (is_dir($filepath)) {
                if (!$this->isEmptyDirectory($filepath)) {
                    $this->io->write(
                        "Composer vendor cleaner: Directory '<info>{$fileToRemove}</info>' from package <info>{$packageName}</info> not removed, because isn't empty",
                        true,
                        IOInterface::VERBOSE
                    );
                    continue;
                }

                $this->filesystem->removeDirectory($filepath);

                $this->io->write(
                    "Composer vendor cleaner: Directory '<info>{$fileToRemove}</info>' from package <info>{$packageName}</info> removed",
                    true,
                    IOInterface::VERBOSE
                );
                $this->removedDirectories++;
            } else {
                $this->filesystem->remove($filepath);

                $this->removedFiles++;
                $this->io->write(
                    "Composer vendor cleaner: File '<info>{$fileToRemove}</info>' from package <info>{$packageName}</info> removed",
                    true,
                    IOInterface::VERBOSE
                );
            }
        }

    }

    /**
     * @param string $packageName
     * @param array $devFiles
     * @return array
     */
    private function findGlobPatternsForPackage($packageName, $devFiles)
    {
        $globPatterns = [];

        $globFilter = new GlobFilter();
        foreach ($devFiles as $packageGlob => $devFile) {
            $packageGlobPattern = rtrim($packageGlob, '/');
            if ($packageGlobPattern === '') {
                $packageGlobPattern = '*/*';
            } elseif (strpos($packageGlobPattern, '/') === false) {
                $packageGlobPattern = '/*';
            }

            $globFilter->clear();
            $globFilter->addInclude($packageGlobPattern, $this->matchCase);
            if (!empty($globFilter->getFilteredEntries([$packageName]))) {
                $globPatterns += $devFile;
            }
        }

        return $globPatterns;
    }

    /**
     * @param array $patterns
     * @return array
     */
    private function buildGlobPatternForFilter($patterns)
    {
        $globPatterns = [];
        foreach ($patterns as $pattern) {
            $filePatternPrefix = '';
            $filePatternSuffix = '';
            if (substr($pattern, 0, 1) !== '/') {
                $filePatternPrefix = '/**/';
            }

            if (substr($pattern, -1) === '/') {
                $filePatternSuffix = '**';
            }

            $globPattern = '/' . ltrim($filePatternPrefix . $pattern . $filePatternSuffix, '/');

            $globPatterns[] = $globPattern;
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
