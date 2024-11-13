<?php

namespace Liborm85\ComposerVendorCleaner\Tests;

use Liborm85\ComposerVendorCleaner\Plugin;

class PluginTest extends TestCase
{

    /**
     * @return void
     */
    public function testGetSubscribedEvents()
    {
        $plugin = $this->getPlugin();
        $constants = $this->getClassConstants('Composer\Script\ScriptEvents');
        $constants += $this->getClassConstants('Composer\Installer\PackageEvents');
        $constants += $this->getClassConstants('Composer\Plugin\PluginEvents');
        foreach ($plugin::getSubscribedEvents() as $event => $method) {
            self::assertContains($event, $constants);
            if (method_exists('\PHPUnit\Framework\TestCase', 'assertIsCallable')) {
                self::assertIsCallable([$plugin, is_array($method) ? $method[0] : $method]);
            } else {
                self::assertInternalType('callable', [$plugin, is_array($method) ? $method[0] : $method]);
            }
        }
    }

    /**
     * @return Plugin
     */
    private function getPlugin()
    {
        return new Plugin();
    }

    /**
     * @param string $classname
     * @return string[]
     * @throws \ReflectionException
     */
    private function getClassConstants($classname)
    {
        if (!class_exists($classname)) {
            throw new \ReflectionException('Class not found');
        }

        $reflection = new \ReflectionClass($classname);

        return $reflection->getConstants();
    }
}
