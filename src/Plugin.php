<?php

namespace Liborm85\ComposerVendorCleaner;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class Plugin implements PluginInterface, EventSubscriberInterface
{

    /**
     * @inheritDoc
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        // TODO: Implement activate() method.
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        // TODO: Implement getSubscribedEvents() method.
    }
}
