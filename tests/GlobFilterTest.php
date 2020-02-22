<?php

namespace Liborm85\ComposerVendorCleaner\Tests;

use Liborm85\ComposerVendorCleaner\GlobFilter;

class GlobFilterTest extends TestCase
{

    /**
     * @var string[]
     */
    private $simpleEntriesArray = [
        'test.php',
        'Test.php',
        'TEST.PHP',
        'test.docx',
        'Test.docx',
        'TEST.DOCX',
    ];

    /**
     * @var string[]
     */
    private $unsortedArray = [
        'ftest.docx',
        'utest.docx',
        'atest.docx',
        'ztest.docx',
        'wtest.docx',
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

    public function testEmptyInclude()
    {
        $globFilter = new GlobFilter();
        self::assertEquals(
            [],
            $globFilter->getFilteredEntries($this->simpleEntriesArray)
        );
    }

    public function testEmptyEntries()
    {
        $globFilter = new GlobFilter();
        $globFilter->addInclude('*.*');
        self::assertEquals(
            [],
            $globFilter->getFilteredEntries([])
        );
    }

    public function testUnorderedArray()
    {
        $globFilter = new GlobFilter();
        $globFilter->addInclude('*.docx');
        self::assertEquals(
            ['ftest.docx', 'utest.docx', 'atest.docx', 'ztest.docx', 'wtest.docx',],
            $globFilter->getFilteredEntries($this->unsortedArray, GlobFilter::ORDER_NONE)
        );
    }

    public function testOrderedAscendingArray()
    {
        $globFilter = new GlobFilter();
        $globFilter->addInclude('*.docx');
        self::assertEquals(
            ['atest.docx', 'ftest.docx', 'utest.docx', 'wtest.docx', 'ztest.docx',],
            $globFilter->getFilteredEntries($this->unsortedArray, GlobFilter::ORDER_ASCENDING)
        );
    }

    public function testOrderedDescendingArray()
    {
        $globFilter = new GlobFilter();
        $globFilter->addInclude('*.docx');
        self::assertEquals(
            ['ztest.docx', 'wtest.docx', 'utest.docx', 'ftest.docx', 'atest.docx',],
            $globFilter->getFilteredEntries($this->unsortedArray, GlobFilter::ORDER_DESCENDING)
        );
    }
}
