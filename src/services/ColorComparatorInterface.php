<?php declare(strict_types = 1);

namespace DIF\Services;

use DIF\Models\ImageColor;

interface ColorComparatorInterface
{
    /**
     * Compare two file resources and return their similarity score
     *
     * @param ImageColor $color1
     * @param ImageColor $color2
     * @return float Return a value <= 1. The closer they are to 1, the more similar they are
     */
    public function compare(ImageColor $color1, ImageColor $color2) : float;
}


