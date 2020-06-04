<?php

use DIF\Services\ClosenessComparator;
use DIF\Services\ClosenessComparatorInterface;
use DIF\Services\ColorAverager;
use DIF\Services\ColorAveragerInterface;
use DIF\Services\ColorComparator;
use DIF\Services\ColorComparatorInterface;
use DIF\Services\DuplicateImageFinder;
use DIF\Services\DuplicateImageFinderInterface;
use DIF\Services\DuplicatesRemover;
use DIF\Services\DuplicatesRemoverInterface;
use DIF\Services\DuplicatesRenderer;
use DIF\Services\DuplicatesRendererInterface;
use DIF\Services\DuplicatesSorter;
use DIF\Services\DuplicatesSorterInterface;
use DIF\Services\ImageComparator;
use DIF\Services\ImageComparatorInterface;
use DIF\Services\ImageResizer;
use DIF\Services\ImageResizerInterface;
use function DI\get;

return [
    ClosenessComparatorInterface::class  => get(ClosenessComparator::class),
    ColorAveragerInterface::class        => get(ColorAverager::class),
    ColorComparatorInterface::class      => get(ColorComparator::class),
    DuplicateImageFinderInterface::class => get(DuplicateImageFinder::class),
    ImageComparatorInterface::class      => get(ImageComparator::class),
    ImageResizerInterface::class         => get(ImageResizer::class),
    DuplicatesRemoverInterface::class    => get(DuplicatesRemover::class),
    DuplicatesRendererInterface::class   => get(DuplicatesRenderer::class),
    DuplicatesSorterInterface::class     => get(DuplicatesSorter::class),
];