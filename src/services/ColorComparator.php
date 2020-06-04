<?php

namespace DIF\Services;

use DIF\Models\ImageColor;

final class ColorComparator implements ColorComparatorInterface
{
    /**
     * @var ClosenessComparatorInterface 
     */
    private $closenessComparator;

    /**
     * Keep track of comparisons to prevent recalculation
     *
     * @var array
     */
    private $knownComparisons = [];

    public function __construct(ClosenessComparatorInterface $closenessComparator)
    {
        $this->closenessComparator = $closenessComparator;
    }

    /**
     * Compare two file resources and return their similarity score
     *
     * @param ImageColor $color1
     * @param ImageColor $color2
     * @return float Return a value <= 1. The closer they are to 1, the more similar they are
     */
    public function compare(ImageColor $color1, ImageColor $color2) : float
    {
        $rgb1 = $color1->getRGBSequence();
        $rgb2 = $color2->getRGBSequence();

        if (!isset($this->knownComparisons[$rgb1][$rgb2])) {
            $redCloseness   = $this->closenessComparator->compare($color1->getRed(), $color2->getRed());
            $greenCloseness = $this->closenessComparator->compare($color1->getGreen(), $color2->getGreen());
            $blueCloseness  = $this->closenessComparator->compare($color1->getBlue(), $color2->getBlue());
            $alphaCloseness = $this->closenessComparator->compare($color1->getAlpha(), $color2->getAlpha());

            $this->knownComparisons[$rgb1][$rgb2] = $redCloseness * $greenCloseness * $blueCloseness;
        }

        return $this->knownComparisons[$rgb1][$rgb2];
    }
}


