<?php

use DIF\Controller\FinderController;
use DIF\Factory\FileResourceFactory;
use DIF\Services\ClosenessComparator;
use DIF\Services\ColorComparator;
use DIF\Services\DuplicateImageFinder;
use DIF\Services\ImageComparator;

require 'vendor/autoload.php';


$colorComparator = new ColorComparator(new ClosenessComparator());
$imageComparator  = new ImageComparator(new ClosenessComparator(), $colorComparator);
$finder           = new DuplicateImageFinder($imageComparator, $colorComparator, new FileResourceFactory());

(new FinderController($finder))->start();


/**
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
 *
 * */