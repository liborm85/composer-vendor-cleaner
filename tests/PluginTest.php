<?php

namespace Liborm85\ComposerVendorCleaner\Tests;

use Liborm85\ComposerVendorCleaner\Plugin;

class PluginTest extends TestCase
{

    public function testGetSubscribedEvents()
    {
        $plugin = $this->getPlugin();
        $reflection = new \ReflectionClass('Composer\Script\ScriptEvents');
        $constants = $reflection->getConstants();
        foreach ($plugin::getSubscribedEvents() as $event => $method) {
            self::assertContains($event, $constants);
            self::assertInternalType('callable', [$plugin, is_array($method) ? $method[0] : $method]);
        }
    }

    /**
     * @return Plugin
     */
    private function getPlugin()
    {
        return new Plugin();
    }
}
