<?php declare(strict_types = 1);

namespace DIF\Services;

use DIF\Factory\FileResourceFactory;
use DIF\Models\DuplicateFile;
use DIF\Models\FileResource;

final class DuplicateImageFinder implements DuplicateImageFinderInterface
{
    /**
     * @var ColorComparator
     */
    private $colorComparator;

    /**
     * @var ImageComparator
     */
    private $imageComparator;

    /**
     * @var FileResourceFactory
     */
    private $imageResourceFactory;

    /**
     * Finder constructor.
     * @param ImageComparator     $comparator
     * @param ColorComparator     $colorComparator
     * @param FileResourceFactory $imageResourceFactory
     */
    public function __construct(
        ImageComparator $comparator,
        ColorComparator $colorComparator,
        FileResourceFactory $imageResourceFactory
    ) {
        $this->imageComparator      = $comparator;
        $this->colorComparator      = $colorComparator;
        $this->imageResourceFactory = $imageResourceFactory;
    }

    /**
     * Scan the directory for duplicates
     *
     * @param string $directory
     * @param int    $threshold
     * @return DuplicateFile[]
     */
    public function scan(string $directory, int $threshold) : array
    {
        $directory = rtrim($directory, '/');
        $files     = $this->getFiles($directory);

        $fileResources = $this->imageResourceFactory->getFileResources(...$files);
        // Sort by color
        usort($fileResources, function (FileResource $a, FileResource $b) {
            return $this->colorComparator->compareRGBString($a->getTotalColorAverage(), $b->getTotalColorAverage());
        });

        $fileCount           = count($fileResources);
        $requiredComparisons = $fileCount * ($fileCount - 1) / 2;
        echo sprintf("Found %d files. %s max comparisons required\n", $fileCount, number_format($requiredComparisons));

        $progress    = 0;
        $comparisons = [];
        $skip        = [];
        for ($i = 0; $i < $fileCount; $i++) {
            $file1 = $fileResources[$i]->getName();
            if (isset($skip[$file1])) {
                continue;
            }
            for ($j = $i + 1; $j < $fileCount; $j++) {
                $file2 = $fileResources[$j]->getName();
                if (isset($skip[$file2])) {
                    continue;
                }
                $percent = ++$progress * 100 / $requiredComparisons;
                echo "\r" . $progress . ' ' . round($percent, 2) . '% ';

                // current image being checked is already outside similarity threshold. Skip this and the next images
                if ($this->colorComparator->compare($fileResources[$i]->getTotalColorAverage(),
                        $fileResources[$j]->getTotalColorAverage()) * 100 < $threshold) {
                    $progress += $fileCount - ($j + 1);
                    break;
                }

                $comparisons[$file1][$file2] = $this->imageComparator->compare($fileResources[$i], $fileResources[$j]);
                if ($comparisons[$file1][$file2] * 100 >= $threshold) {
                    $skip[$file1] = 1;
                    $skip[$file2] = 1;
                }
            }
        }


        return $this->formatOutputToDuplicateFiles($fileResources, $comparisons, $threshold);
    }

    /**
     * @param FileResource[] $files
     * @param array          $result
     *
     * @param int            $thresholdPercent
     * @return DuplicateFile[]
     */
    private function formatOutputToDuplicateFiles(array &$files, array $result, int $thresholdPercent) : array
    {
        // Index the files
        /**
         * @var FileResource[] $filesByName
         */
        $filesByName = [];
        foreach ($files as $file) {
            $filesByName[$file->getName()] = $file;
        }

        $return = [];
        foreach ($result as $basisFile => $duplicates) {
            $resultFile = new DuplicateFile($basisFile, $filesByName[$basisFile]->getSize(), true);
            foreach ($duplicates as $duplicate => $score) {
                if ($score * 100 >= $thresholdPercent) {
                    $resultFile->addDuplicate($duplicate, $filesByName[$duplicate]->getSize(), $score);
                }
            }
            if (!empty($resultFile->getAlternates())) {
                // Add self in the group
                $resultFile->addDuplicate($resultFile->getFilename(), $resultFile->getFilesize(), $resultFile->getScore(), true);
                $return[] = $resultFile;
            }
        }

        return $return;
    }

    /**
     * Get the files from the given directory
     *
     * @param $directory
     *
     * @return array
     */
    private function getFiles($directory)
    {
        $filesAndFolders = scandir($directory);
        $files           = [];
        foreach ($filesAndFolders as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $directory . '/' . $file;
            if (!is_dir($path)) {
                $files[] = $path;
            }
        }

        foreach ($filesAndFolders as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $directory . '/' . $file;
            if (is_dir($path)) {
                $files = array_merge($files, $this->getFiles($path));
            }
        }

        return $files;
    }
}
