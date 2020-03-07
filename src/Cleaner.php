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
     * @var bool
     */
    private $matchCase;

    /**
     * @var array
     */
    private $devFiles;

    /**
     * @var bool
     */
    private $removeEmptyDirs;

    /**
     * @var int
     */
    private $packagesCount;

    /**
     * @var int
     */
    private $removedDirectories = 0;

    /**
     * @var int
     */
    private $removedFiles = 0;

    /**
     * @var int
     */
    private $removedEmptyDirectories = 0;

    /**
     * @param IOInterface $io
     * @param Filesystem $filesystem
     * @param array $devFiles
     * @param bool $matchCase
     * @param bool $removeEmptyDirs
     */
    public function __construct($io, $filesystem, $devFiles, $matchCase, $removeEmptyDirs)
    {
        $this->io = $io;
        $this->filesystem = $filesystem;
        $this->devFiles = $devFiles;
        $this->matchCase = $matchCase;
        $this->removeEmptyDirs = $removeEmptyDirs;
    }

    /**
     * @param Package[] $packages
     */
    public function cleanupPackages($packages)
    {
        $this->io->write("");
        $this->io->write("Composer vendor cleaner: <info>Cleaning packages in vendor directory</info>");

        $this->packagesCount = count($packages);

        $devFilesFinder = new DevFilesFinder($this->devFiles, $this->matchCase);

        foreach ($packages as $package) {
            $devFilesPatternsForPackage = $devFilesFinder->getGlobPatternsForPackage($package->getPrettyName());
            if (empty($devFilesPatternsForPackage)) {
                continue;
            }

            $allFiles = $this->getDirectoryEntries($package->getInstallPath());
            $filesToRemove = $devFilesFinder->getFilteredEntries($allFiles, $devFilesPatternsForPackage);

            $this->removeFiles($package->getPrettyName(), $package->getInstallPath(), $filesToRemove);
        }

        if ($this->removeEmptyDirs) {
            foreach ($packages as $package) {
                $this->removeEmptyDirectories($package->getInstallPath());
            }
        }
    }

    /**
     * @param string $binDir
     */
    public function cleanupBinary($binDir)
    {
        if (!file_exists($binDir)) {
            return;
        }
        
        $this->io->write("Composer vendor cleaner: <info>Cleaning vendor binary directory</info>");

        $devFilesFinder = new DevFilesFinder($this->devFiles, $this->matchCase);

        $devFilesPatternsForBin = $devFilesFinder->getGlobPatternsForPackage('bin');
        if (!empty($devFilesPatternsForBin)) {
            $allFiles = $this->getDirectoryEntries($binDir);
            $filesToRemove = $devFilesFinder->getFilteredEntries($allFiles, $devFilesPatternsForBin);

            $this->removeFiles('bin', $binDir, $filesToRemove);
        }

        if ($this->removeEmptyDirs) {
            $this->removeEmptyDirectories($binDir);
        }
    }

    public function finishCleanup()
    {
        if ($this->removedEmptyDirectories) {
            $this->io->write(
                "Composer vendor cleaner: <info>Removed {$this->removedFiles} files and {$this->removedDirectories} (of which {$this->removedEmptyDirectories} are empty) directories from {$this->packagesCount} packages</info>"
            );
        } else {
            $this->io->write(
                "Composer vendor cleaner: <info>Removed {$this->removedFiles} files and {$this->removedDirectories} directories from {$this->packagesCount} packages</info>"
            );
        }
        $this->io->write("");
    }

    /**
     * @param string $path
     */
    private function removeEmptyDirectories($path)
    {
        $directory = new Directory();
        $directory->addPath($path);
        $directories = $directory->getDirectories();
        rsort($directories);

        foreach ($directories as $directory) {
            $filepath = $path . $directory;
            if (!$this->isEmptyDirectory($filepath)) {
                continue;
            }

            $this->filesystem->removeDirectory($filepath);
            $this->removedDirectories++;
            $this->removedEmptyDirectories++;
        }

        if ($this->isEmptyDirectory($path)) {
            $this->filesystem->removeDirectory($path);
            $this->removedDirectories++;
            $this->removedEmptyDirectories++;
        }
    }

    /**
     * @param string $packageName
     * @param string $rootDir
     * @param array $filesToRemove
     */
    private function removeFiles($packageName, $rootDir, $filesToRemove)
    {
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
     * @param $path
     * @return array
     */
    private function getDirectoryEntries($path)
    {
        $directory = new Directory();
        $directory->addPath($path);

        return $directory->getEntries();
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
