<?php declare(strict_types = 1);

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
    public function sortByScoreGroup(DuplicateFile $file1, DuplicateFile $file2) : int;

    /**
     * usorter
     *
     * @param DuplicateFile $file1
     * @param DuplicateFile $file2
     *
     * @return float
     */
    public function sortBySize(DuplicateFile $file1, DuplicateFile $file2) : float;
}
