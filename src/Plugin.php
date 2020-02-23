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
     * @var bool
     */
    private $isCleaned = false;

    /**
     * @var array
     */
    private $changedPackages = [];

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::PRE_AUTOLOAD_DUMP => 'cleanup',
            ScriptEvents::POST_UPDATE_CMD => 'cleanup',
            ScriptEvents::POST_INSTALL_CMD => 'cleanup',
            PackageEvents::POST_PACKAGE_INSTALL => 'addPackage',
            PackageEvents::POST_PACKAGE_UPDATE => 'addPackage',
        ];
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
    }

    public function addPackage(PackageEvent $event)
    {
        /** @var InstallOperation|UpdateOperation $operation */
        $operation = $event->getOperation();
        $this->changedPackages[] = $operation->getPackage()->getPrettyName();
    }

    public function cleanup(Event $event)
    {
        if ($this->isCleaned) { // fire event only once
            return;
        }

        $this->isCleaned = true;

        $package = $this->composer->getPackage();
        $extra = $package->getExtra();
        $devFiles = isset($extra[self::DEV_FILES_KEY]) ? $extra[self::DEV_FILES_KEY] : null;
        if (!$devFiles) {
            return;
        }

        $pluginConfig = $this->config->get(self::DEV_FILES_KEY);
        $matchCase = isset($pluginConfig['match-case']) ? (bool)$pluginConfig['match-case'] : true;

        $vendorDir = $this->config->get('vendor-dir');
        $binDir = $this->config->get('bin-dir');

        $packages = $this->getPackages();

        $cleaner = new Cleaner($this->io, $this->filesystem, $vendorDir, $binDir, $packages, $matchCase);
        $cleaner->cleanup($devFiles);
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
