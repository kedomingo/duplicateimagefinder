<?php declare(strict_types = 1);

namespace DIF\Services;

use DIF\Models\DuplicateFile;

final class DuplicatesRenderer implements DuplicatesRendererInterface
{
    /**
     * @var DuplicatesSorterInterface
     */
    private $sorter;

    /**
     * DuplicatesRenderer constructor.
     * @param DuplicatesSorterInterface $sorter
     */
    public function __construct(DuplicatesSorterInterface $sorter)
    {
        $this->sorter = $sorter;
    }

    /**
     * @param bool          $isPrioritizeMatch
     * @param DuplicateFile ...$files
     */
    public function render(bool $isPrioritizeMatch, DuplicateFile ...$files)
    {
        echo "\n-------------------------\n";
        echo "Result\n";
        echo "-------------------------\n\n";
        foreach ($files as $filegroup) {
            $duplicates = $filegroup->getAlternates();
            if ($isPrioritizeMatch) {
                usort($duplicates, [$this->sorter, 'sortByScoreGroup']);
            } else {
                usort($duplicates, [$this->sorter, 'sortBySize']);
            }
            foreach ($duplicates as $k => $duplicate) {
                if ($k > 0) {
                    echo '    ';
                }
                echo ($duplicate->isBasisFile() ? '* ' : '') . $duplicate->getFilename() . "\n";
            }
            echo "\n";
        }
    }
}