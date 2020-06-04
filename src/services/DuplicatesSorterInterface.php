<?php

namespace DIF\Services;

use DIF\Models\DuplicateFile;

interface DuplicatesSorterInterface
{
    /**
     * usorter
     *
     * @param DuplicateFile $file1
     * @param DuplicateFile $file2
     *
     * @return int
     */
    public function sortByScore(DuplicateFile $file1, DuplicateFile $file2) : int;

    /**
     * usorter
     *
     * @param DuplicateFile $file1
     * @param DuplicateFile $file2
     *
     * @return int
     */
    public function sortBySize(DuplicateFile $file1, DuplicateFile $file2) : int;
}
