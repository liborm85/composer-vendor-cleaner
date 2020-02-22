<?php

namespace Liborm85\ComposerVendorCleaner;

class DevFilesFinder
{
    /**
     * @var array
     */
    private $devFiles;

    /**
     * @var bool
     */
    private $matchCase;

    /**
     * @param array $devFiles
     * @param bool $matchCase
     */
    public function __construct($devFiles, $matchCase)
    {
        $this->devFiles = $devFiles;
        $this->matchCase = $matchCase;
    }

    /**
     * @param string $packageName
     * @return array
     */
    public function getGlobPatternsForPackage($packageName)
    {
        $globPatterns = [];

        $globFilter = new GlobFilter();
        foreach ($this->devFiles as $packageGlob => $devFile) {
            $packageGlobPattern = rtrim($packageGlob, '/');
            if ($packageGlobPattern === '') {
                $packageGlobPattern = '*/*';
            } elseif (strpos($packageGlobPattern, '/') === false) {
                $packageGlobPattern = '/*';
            }

            $globFilter->clear();
            $globFilter->addInclude($packageGlobPattern, $this->matchCase);
            if (!empty($globFilter->getFilteredEntries([$packageName]))) {
                $globPatterns = array_merge($globPatterns, $devFile);
            }
        }

        return $globPatterns;
    }

    /**
     * @param array $entries
     * @param array $globPatterns
     * @return array
     */
    public function getFilteredEntries($entries, $globPatterns)
    {
        $globPatterns = $this->buildGlobPatternForFilter($globPatterns);

        $globFilter = new GlobFilter();
        foreach ($globPatterns as $globPattern) {
            $globFilter->addInclude($globPattern, $this->matchCase);
        }

        return $globFilter->getFilteredEntries($entries, GlobFilter::ORDER_DESCENDING);
    }

    /**
     * @param array $patterns
     * @return array
     */
    private function buildGlobPatternForFilter($patterns)
    {
        $globPatterns = [];
        foreach ($patterns as $pattern) {
            $filePatternPrefix = '';
            $filePatternSuffix = '';
            if (substr($pattern, 0, 1) !== '/') {
                $filePatternPrefix = '/**/';
            }

            if (substr($pattern, -1) === '/') {
                $filePatternSuffix = '**';
            }

            $globPattern = '/' . ltrim($filePatternPrefix . $pattern . $filePatternSuffix, '/');

            $globPatterns[] = $globPattern;
        }

        return $globPatterns;
    }

}
