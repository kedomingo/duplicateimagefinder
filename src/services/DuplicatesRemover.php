<?php

namespace DIF\Services;

use DIF\Models\DuplicateFile;

final class DuplicatesRemover implements DuplicatesRemoverInterface {
    /**
     * @var DuplicatesSorterInterface
     */
    private $sorter;

    /**
     * DuplicatesRemover constructor.
     * @param DuplicatesSorterInterface $sorter
     */
    public function __construct(DuplicatesSorterInterface $sorter)
    {
        $this->sorter = $sorter;
    }

    /**
     * @param bool          $isPrioritizeMatch
     * @param string        $destinationDir
     * @param DuplicateFile ...$files
     * @return mixed
     */
    public function moveDuplicates(bool $isPrioritizeMatch, string $destinationDir, DuplicateFile ...$files)
    {
        foreach ($files as $filegroup) {
            $duplicates = $filegroup->getAlternates();
            if ($isPrioritizeMatch) {
                usort($duplicates, [$this->sorter, 'sortByScore']);
            } else {
                usort($duplicates, [$this->sorter, 'sortBySize']);
            }
            foreach ($duplicates as $k => $duplicate) {
                if ($k === 0) {
                    continue;
                }
                $newpath = $destinationDir . '/' . $duplicate->getFilename();
                if (!file_exists(dirname($newpath))) {
                    mkdir(dirname($newpath), 0777, true);
                }
                rename($duplicate->getFilename(), $newpath);
            }
        }

        echo "\n\nMoved the duplicates to $destinationDir\n\n";
    }
}