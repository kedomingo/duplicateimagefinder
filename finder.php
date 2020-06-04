<?php

use DIF\Controller\FinderController;

require 'vendor/autoload.php';

// Build DI container
$containerBuilder = new DI\ContainerBuilder();
$containerBuilder->addDefinitions('config/services.php');
$container = $containerBuilder->build();

$duplicateImageFinderController = $container->get(FinderController::class);
$duplicateImageFinderController->start();