<?php declare(strict_types = 1);

namespace DIF\Services;

use DIF\Models\DuplicateFile;

final class DuplicatesSorter implements DuplicatesSorterInterface
{
    /**
     * usorter
     *
     * @param DuplicateFile $file1
     * @param DuplicateFile $file2
     *
     * @return int
     */
    public function sortByScoreGroup(DuplicateFile $file1, DuplicateFile $file2) : int
    {
        $percentGroup1 = floor($file1->getScore() * 10);
        $percentGroup2 = floor($file2->getScore() * 10);
        if ($percentGroup1 != $percentGroup2) {
            return (int)($percentGroup2 - $percentGroup1);
        }

        return $file2->getFilesize() - $file1->getFilesize();
    }

    /**
     * usorter
     *
     * @param DuplicateFile $file1
     * @param DuplicateFile $file2
     *
     * @return float
     */
    public function sortBySize(DuplicateFile $file1, DuplicateFile $file2) : float
    {
        return ($file2->getFilesize() * $file2->getScore()) - ($file1->getFilesize() * $file1->getScore());
    }
}
