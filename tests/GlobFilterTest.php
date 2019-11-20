<?php

namespace Liborm85\ComposerVendorCleaner\Tests;

use Liborm85\ComposerVendorCleaner\GlobFilter;

class GlobFilterTest extends TestCase
{
    private $simpleEntriesArray = [
        'test.php',
        'Test.php',
        'TEST.PHP',
        'test.docx',
        'Test.docx',
        'TEST.DOCX',
    ];

    public function testListOfFilesMatchCase()
    {
        $globFilter = new GlobFilter();
        $globFilter->addInclude('*.php');
        self::assertEquals(
            ['test.php', 'Test.php'],
            $globFilter->getFilteredEntries($this->simpleEntriesArray)
        );
    }

    public function testListOfFilesNoMatchCase()
    {
        $globFilter = new GlobFilter();
        $globFilter->addInclude('*.php', false);
        self::assertEquals(
            ['test.php', 'Test.php', 'TEST.PHP'],
            $globFilter->getFilteredEntries($this->simpleEntriesArray)
        );
    }
}
