<?php

namespace Liborm85\ComposerVendorCleaner;

use Composer\Composer;
use Composer\Config;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    const DEV_FILES_KEY = 'dev-files';

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var Cleaner
     */
    private $cleaner;

    /**
     * @var bool
     */
    private $isCleanedPackages = false;

    /**
     * @var array
     */
    private $changedPackages = [];

    /**
     * @var bool
     */
    private $actionIsDumpAutoload = true;

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::PRE_AUTOLOAD_DUMP => 'cleanup',
            ScriptEvents::PRE_UPDATE_CMD => 'preInstall',
            ScriptEvents::PRE_INSTALL_CMD => 'preInstall',
            ScriptEvents::POST_UPDATE_CMD => 'cleanup',
            ScriptEvents::POST_INSTALL_CMD => 'cleanup',
            PackageEvents::POST_PACKAGE_INSTALL => 'addPackage',
            PackageEvents::POST_PACKAGE_UPDATE => 'addPackage',
        ];
    }

    public function preInstall(Event $event)
    {
        $this->actionIsDumpAutoload = false;
    }

    /**
     * @inheritDoc
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->config = $composer->getConfig();
        $this->filesystem = new Filesystem();

        $package = $this->composer->getPackage();
        $extra = $package->getExtra();
        $devFiles = isset($extra[self::DEV_FILES_KEY]) ? $extra[self::DEV_FILES_KEY] : null;
        if ($devFiles) {
            $pluginConfig = $this->config->get(self::DEV_FILES_KEY);
            $matchCase = isset($pluginConfig['match-case']) ? (bool)$pluginConfig['match-case'] : true;
            $removeEmptyDirs = isset($pluginConfig['remove-empty-dirs']) ? (bool)$pluginConfig['remove-empty-dirs'] : true;

            $this->cleaner = new Cleaner($this->io, $this->filesystem, $devFiles, $matchCase, $removeEmptyDirs);
        }
    }

    public function addPackage(PackageEvent $event)
    {
        /** @var InstallOperation|UpdateOperation $operation */
        $operation = $event->getOperation();
        $this->changedPackages[] = $operation->getPackage()->getPrettyName();
    }

    public function cleanup(Event $event)
    {
        if (!$this->cleaner) { // cleaner not enabled/configured in project
            return;
        }

        if (!$this->isCleanedPackages) {
            $this->cleaner->cleanupPackages($this->getPackages());
        }

        if ($this->actionIsDumpAutoload || $this->isCleanedPackages) {
            $this->cleaner->cleanupBinary($this->config->get('bin-dir'));
            $this->cleaner->finishCleanup();
        }

        $this->isCleanedPackages = true;
    }

    /**
     * @return Package[]
     */
    private function getPackages()
    {
        $packages = [];
        $localRepository = $this->composer->getRepositoryManager()->getLocalRepository();
        $installationManager = $this->composer->getInstallationManager();
        foreach ($localRepository->getPackages() as $repositoryPackage) {
            $package = new Package(
                $repositoryPackage,
                $installationManager,
                in_array($repositoryPackage->getPrettyName(), $this->changedPackages)
            );
            $packages[] = $package;
        }

        return $packages;
    }

}
