<?php

namespace Liborm85\ComposerVendorCleaner;

use Composer\Composer;
use Composer\Config;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Util\Filesystem;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    const EXTRA_KEY = 'dev-files';

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
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::PRE_AUTOLOAD_DUMP => 'cleanup',
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

    public function cleanup(Event $event)
    {
        $package = $this->composer->getPackage();
        $extra = $package->getExtra();
        $devFiles = isset($extra[self::EXTRA_KEY]) ? $extra[self::EXTRA_KEY] : null;
        if (!$devFiles) {
            return;
        }

        $vendorDir = $this->config->get('vendor-dir');

        $cleaner = new Cleaner($vendorDir);
        $cleaner->cleanup($devFiles);

        var_dump('Not implemented ' . $event->getName() . ' callback');
    }

}
