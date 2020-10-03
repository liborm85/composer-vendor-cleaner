<?php

namespace Liborm85\ComposerVendorCleaner\Tests;

use Liborm85\ComposerVendorCleaner\DevFilesFinder;

class DevFilesFinderTest extends TestCase
{
    /**
     * @var string[]
     */
    private $simpleEntriesArray = [
        '/src/file.php',
        '/src/File.php',
        '/src/FILE.PHP',
        '/tests/test.php',
        '/tests/Test.php',
        '/tests/TEST.PHP',
        '/tests/test.docx',
        '/tests/Test.docx',
        '/tests/TEST.DOCX',
    ];

    public function testGetGlobPatternsForPackage()
    {
        $devFiles = [
            '/' => [
                'all-directories',
            ],
            'bin' => [
                'bin-directory',
            ],
            'binabc' => [
                'it-is-not-bin-directory',
            ],
            '*/*' => [
                'all-packages',
            ],
            'fake/*' => [
                'fake-namespace',
            ],
            'fake/' => [
                'fake-namespace2',
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
        $devFilesFinder = new DevFilesFinder($devFiles, false);
        self::assertEquals(
            ['all-directories', 'all-packages', 'fake-namespace', 'fake-namespace2', 'all-namespaces-and-package',],
            $devFilesFinder->getGlobPatternsForPackage('fake/package')
        );
        self::assertEquals(
            ['all-directories', 'all-packages', 'fake-namespace', 'fake-namespace2',],
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
        self::assertEquals(
            ['all-directories', 'bin-directory',],
            $devFilesFinder->getGlobPatternsForPackage('bin')
        );
    }

    public function testGetFilteredEntries1()
    {
        $patterns = [
            '/tests/**',
        ];

        $devFiles = [];
        $devFilesFinder = new DevFilesFinder($devFiles, false);
        self::assertEquals(
            [
                '/tests/test.php',
                '/tests/test.docx',
                '/tests/Test.php',
                '/tests/Test.docx',
                '/tests/TEST.PHP',
                '/tests/TEST.DOCX',
            ],
            $devFilesFinder->getFilteredEntries($this->simpleEntriesArray, $patterns)
        );
    }

    public function testGetFilteredEntries2()
    {
        $patterns = [
            '/tests/**',
            '!/tests/*.docx',
        ];

        $devFiles = [];
        $devFilesFinder = new DevFilesFinder($devFiles, false);
        self::assertEquals(
            [
                '/tests/test.php',
                '/tests/Test.php',
                '/tests/TEST.PHP',
            ],
            $devFilesFinder->getFilteredEntries($this->simpleEntriesArray, $patterns)
        );
    }

}
