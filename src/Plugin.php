<?php

namespace Liborm85\ComposerVendorCleaner;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;

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
        return [
            ScriptEvents::PRE_AUTOLOAD_DUMP => 'cleanup',
        ];
    }

    public function cleanup(Event $event)
    {
        var_dump('Not implemented '.$event->getName().' callback');
    }
}
