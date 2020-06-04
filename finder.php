<?php

require 'classes/ImageResourceCollection.php';
require 'classes/ImageColor.php';
require 'classes/ImageResource.php';
require 'classes/ImageResizer.php';
require 'classes/ImageComparator.php';
require 'classes/UnsupportedImageException.php';
require 'classes/ImageResourceFactory.php';
require 'classes/FileInfo.php';

class Finder
{
    private $comparator;

    /**
     * @var ImageResourceFactory
     */
    private $imageResourceFactory;

    /**
     * Finder constructor.
     * @param ImageComparator      $comparator
     * @param ImageResourceFactory $imageResourceFactory
     */
    public function __construct(
        ImageComparator $comparator,
        ImageResourceFactory $imageResourceFactory
    ) {
        $this->comparator           = $comparator;
        $this->imageResourceFactory = $imageResourceFactory;
    }

    /**
     * Scan the directory for duplicates
     *
     * @param string $directory
     * @param int    $threshold
     * @return array
     * @throws Exception
     */
    public function scan(string $directory, int $threshold)
    {
        $directory = rtrim($directory, '/');
        $files     = $this->getFiles($directory);

        $imageResources = $this->imageResourceFactory->getImageResources(...$files);
        usort($imageResources, function (ImageResource $a, ImageResource $b) {
            return $a->compareColorAverageTo($b);
        });



        $fileCount           = count($imageResources);
        $requiredComparisons = $fileCount * ($fileCount - 1) / 2;
        echo sprintf("Found %d files. %s max comparisons required\n", $fileCount, number_format($requiredComparisons));

        $progress    = 0;
        $comparisons = [];
        $skip        = [];
        for ($i = 0; $i < $fileCount; $i++) {
            $file1 = $imageResources[$i]->getFilename();
            if (isset($skip[$file1])) {
                // continue;
            }
            for ($j = $i + 1; $j < $fileCount; $j++) {
                $file2 = $imageResources[$j]->getFilename();
                if (isset($skip[$file2])) {
                    // continue;
                }
                $percent = ++$progress * 100 / $requiredComparisons;
                // echo "\r" . $progress . ' ' . round($percent, 2) . '% ';

                // current image being checked is already outside similarity threshold. Skip this and the next images
                if ($imageResources[$i]->getTotalColorAverage()->compareTo($imageResources[$j]->getTotalColorAverage()) * 100 < $threshold) {
                    $progress += $fileCount - ($j + 1);
                    echo "Breaking\n";
                    // break;
                }

                echo "Checking $file1 and $file2...\n";
                $comparisons[$file1][$file2] = $this->comparator->compare($imageResources[$i], $imageResources[$j]);
                if ($comparisons[$file1][$file2] * 100 >= $threshold) {
                    $skip[$file1] = 1;
                    $skip[$file2] = 1;
                }
            }
        }

        return $comparisons;
    }

    public function getFiles($directory)
    {
        $filesAndFolders = scandir($directory);
        $files           = [];
        foreach ($filesAndFolders as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $path = $directory . '/' . $file;
            if (is_dir($path)) {
                $files = array_merge($files, $this->getFiles($path));
            } else {
                $files[] = $path;
            }
        }

        return $files;
    }
}


$comparator       = new ImageComparator();
$factory          = new ImageResourceFactory();
$finder           = new Finder($comparator, $factory);
$defaultThreshold = 60;

// Directory
$shortopts = "d:";
$shortopts .= "t:";
$shortopts .= "m::";

$longopts = array(
    "dir:",
    "threshold:",
    "match-priority::",
    "move-duplicates",
);

$options = getopt($shortopts, $longopts);

$directory       = isset($options['d']) ? $options['d'] : (isset($options['dir']) ? $options['dir'] : null);
$threshold       = isset($options['t']) ? $options['t'] : (isset($options['threshold']) ? $options['threshold'] : $defaultThreshold);
$prioritizeMatch = isset($options['m']) || isset($options['match-priority']);
$moveDuplicates  = isset($options['move-duplicates']);

if (empty($directory) || empty($threshold)) {
    echo "You must define the scan directory and duplicate threshold percent using -d and -t options\n";
    exit;
}
if (!is_numeric($threshold) || $threshold < 1 || $threshold > 100) {
    echo "Threshold must be a number between 1 and 100\n";
    exit;
}

if ($prioritizeMatch) {
    echo "Priotitizing match percentage is faulty when you want to keep bigger files and there are duplicate smaller files. Use with caution. Press any key to continue. Ctrl+C to stop";
    readline();
}

$result = $finder->scan($directory, $threshold);

print_r($result);
exit;

$foundDuplicates = [];
foreach ($result as $file1 => $duplicates) {
    $maxscore = 0;
    foreach ($duplicates as $file2 => $score) {
        if ($score * 100 >= $threshold) {
            $maxscore                  = max($maxscore, $score);
            $foundDuplicates[$file1][] = [
                'file'  => $file2,
                'score' => $score,
                'size'  => filesize($file2)
            ];
        }
    }
    // If duplicates are set, add the base image and set the score to the max match score

    if (isset($foundDuplicates[$file1])) {
        $foundDuplicates[$file1][] = [
            'file'  => $file1,
            'score' => $maxscore,
            'size'  => filesize($file1),
            'base'  => true
        ];
    }

    if (isset($foundDuplicates[$file1])) {
        usort($foundDuplicates[$file1], function ($a, $b) use ($prioritizeMatch) {
            if ($prioritizeMatch) {
                $percentGroup1 = floor($a['score'] * 10);
                $percentGroup2 = floor($b['score'] * 10);
                if ($percentGroup1 != $percentGroup2) {
                    return $percentGroup2 - $percentGroup1;
                }

                return $b['size'] - $a['size'];
            }

            return ($b['size'] * $b['score']) - ($a['size'] * $a['score']);
        });
    }
}

echo "\n\n\n-----------------------------------\n";
echo "Results\n";
echo "-----------------------------------\n\n\n";

foreach ($foundDuplicates as $group => $duplicates) {
    foreach ($duplicates as $k => $duplicate) {
        if ($k > 0) {
            echo "    ";
        }
        echo (isset($duplicate['base']) ? '* ' : '') . $duplicate['file'] . ' ' . round($duplicate['size'] / 1024) . 'kB' . ' - ' . round($duplicate['score'] * 100) . '% match' . "\n";
    }
}

if (!$moveDuplicates) {
    echo "\n\nAdd option --move-duplicates to move the duplicates found into a backup directory";
} else {

    $duplicateFolder = 'duplicates_backup';

    foreach ($foundDuplicates as $group => $duplicates) {
        foreach ($duplicates as $k => $duplicate) {
            if ($k === 0) {
                continue;
            }
            $newpath = $duplicateFolder . '/' . $duplicate['file'];
            mkdir(dirname($newpath), 0777, true);
            rename($duplicate['file'], $newpath);
        }
    }
    echo "\n\nMoved the duplicates to $duplicateFolder";

}

echo "\n\n\n";