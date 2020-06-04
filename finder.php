<?php

use DIF\Controller\FinderController;

require 'vendor/autoload.php';

// Build DI container
$containerBuilder = new DI\ContainerBuilder();
$containerBuilder->addDefinitions('config/services.php');
$container = $containerBuilder->build();

$duplicateImageFinderController = $container->get(FinderController::class);
$duplicateImageFinderController->start();


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