<?php declare(strict_types = 1);

namespace DIF\Services;

use DIF\Models\ImageColor;
use DIF\Models\ImageResource;

interface ColorAveragerInterface {

    /**
     * @param ImageResource $imageResource
     * @return ImageColor
     */
    public function getAverageColor(ImageResource $imageResource) : ImageColor;

}