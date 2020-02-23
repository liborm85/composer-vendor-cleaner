<?php

namespace Liborm85\ComposerVendorCleaner\Tests;

use Liborm85\ComposerVendorCleaner\DevFilesFinder;

class DevFilesFinderTest
{

    public function testGetGlobPatternsForPackage()
    {
        $devFiles = [
            '/' => [
                'all-directories',
            ],
            '*/*' => [
                'all-packages',
            ],
            'fake/*' => [
                'fake-namespace',
            ],
            '*/package' => [
                'all-namespaces-and-package',
            ],
            'fake2/package' => [
                'fake2-namespace',
            ],
            '*/package2' => [
                'all-namespaces-and-package2',
            ],
        ];
        $devFilesFinder = new DevFilesFinder($devFiles);
        self::assertEquals(
            ['all-directories', 'all-packages', 'fake-namespace', 'all-namespaces-and-package',],
            $devFilesFinder->getGlobPatternsForPackage('fake/package')
        );
        self::assertEquals(
            ['all-directories', 'all-packages', 'fake-namespace',],
            $devFilesFinder->getGlobPatternsForPackage('fake/otherpackage')
        );
        self::assertEquals(
            ['all-directories', 'all-packages', 'all-namespaces-and-package',],
            $devFilesFinder->getGlobPatternsForPackage('otherfake/package')
        );
        self::assertEquals(
            ['all-directories', 'all-packages',],
            $devFilesFinder->getGlobPatternsForPackage('otherfake/otherpackage')
        );
    }

}
