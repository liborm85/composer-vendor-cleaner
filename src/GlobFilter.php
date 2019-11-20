<?php

namespace Liborm85\ComposerVendorCleaner;

use Liborm85\ComposerVendorCleaner\Finder\Glob;

class GlobFilter
{

    /**
     * @var array
     */
    private $includeRegex = [];

    /**
     * @var array
     */
    private $excludeRegex = [];

    /**
     * @param string $globPattern
     * @param bool $matchCase
     */
    public function addInclude($globPattern, $matchCase = true)
    {
        $this->includeRegex[] = $this->globPatternToRegexPattern($globPattern, $matchCase);
    }

    /**
     * @param string $globPattern
     * @param bool $matchCase
     */
    public function addExclude($globPattern, $matchCase = true)
    {
        $this->excludeRegex[] = $this->globPatternToRegexPattern($globPattern, $matchCase);
    }

    /**
     * @param array $entries
     * @return array
     */
    public function getFilteredEntries($entries)
    {
        if (empty($entries) || empty($this->includeRegex)) {
            return [];
        }

        $includedEntries = $this->filterEntries($this->includeRegex, $entries);

        if (empty($excludedEntries)) {
            return $includedEntries;
        }

        $excludedEntries = $this->filterEntries($this->excludeRegex, $entries);

        return array_diff($includedEntries, $excludedEntries);
    }

    /**
     * @param array $regexPatterns
     * @param array $entries
     * @return array
     */
    private function filterEntries($regexPatterns, $entries)
    {
        $filteredEntries = [];
        foreach ($regexPatterns as $regexPattern) {
            $filteredEntries += preg_grep($regexPattern, $entries);
        }

        return array_unique($filteredEntries);
    }

    /**
     * @param string $globPattern
     * @param bool $matchCase
     * @return string
     */
    private function globPatternToRegexPattern($globPattern, $matchCase = true)
    {
        $regexPattern = Glob::toRegex($globPattern, false);
        if (!$matchCase) {
            $regexPattern .= 'i';
        }

        return $regexPattern;
    }
}
