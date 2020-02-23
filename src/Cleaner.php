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
     * @var string
     */
    private $binDir;

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
     * @param string $binDir
     * @param Package[] $packages
     * @param bool $matchCase
     */
    public function __construct($io, $filesystem, $vendorDir, $binDir, $packages, $matchCase)
    {
        $this->io = $io;
        $this->filesystem = $filesystem;
        $this->vendorDir = $vendorDir;
        $this->binDir = $binDir;
        $this->packages = $packages;
        $this->matchCase = $matchCase;
    }

    /**
     * @param array $devFiles
     */
    public function cleanup($devFiles)
    {
        $this->removedDirectories = 0;
        $this->removedFiles = 0;

        $this->io->write("");
        $this->io->write("Composer vendor cleaner: <info>Cleaning vendor directory</info>");

        $devFilesFinder = new DevFilesFinder($devFiles, $this->matchCase);

        foreach ($this->packages as $package) {
            $devFilesPatternsForPackage = $devFilesFinder->getGlobPatternsForPackage($package->getPrettyName());
            if (empty($devFilesPatternsForPackage)) {
                continue;
            }

            $allFiles = $this->getDirectoryEntries($package->getInstallPath());
            $filesToRemove = $devFilesFinder->getFilteredEntries($allFiles, $devFilesPatternsForPackage);

            $this->removeFiles($package->getPrettyName(), $package->getInstallPath(), $filesToRemove);
        }

        $devFilesPatternsForBin = $devFilesFinder->getGlobPatternsForPackage('bin');
        if (!empty($devFilesPatternsForBin)) {
            $allFiles = $this->getDirectoryEntries($this->binDir);
            $filesToRemove = $devFilesFinder->getFilteredEntries($allFiles, $devFilesPatternsForBin);

            $this->removeFiles('bin', $this->binDir, $filesToRemove);
        }

        $packagesCount = count($this->packages);

        $this->io->write(
            "Composer vendor cleaner: <info>Removed {$this->removedFiles} files and {$this->removedDirectories} directories from {$packagesCount} packages</info>"
        );
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
