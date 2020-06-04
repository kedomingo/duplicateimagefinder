<?php

use DIF\Services\ClosenessComparator;
use DIF\Services\ClosenessComparatorInterface;
use DIF\Services\ColorComparator;
use DIF\Services\ColorComparatorInterface;
use DIF\Services\DuplicateImageFinder;
use DIF\Services\DuplicateImageFinderInterface;
use DIF\Services\ImageComparator;
use DIF\Services\ImageComparatorInterface;
use DIF\Services\ImageResizer;
use DIF\Services\ImageResizerInterface;
use function DI\get;

return [
    ClosenessComparatorInterface::class  => get(ClosenessComparator::class),
    ColorComparatorInterface::class      => get(ColorComparator::class),
    DuplicateImageFinderInterface::class => get(DuplicateImageFinder::class),
    ImageComparatorInterface::class      => get(ImageComparator::class),
    ImageResizerInterface::class         => get(ImageResizer::class),
];