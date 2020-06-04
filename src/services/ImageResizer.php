<?php

namespace DIF\Services;

use DIF\Models\ImageResource;

final class ImageResizer implements ImageResizerInterface
{
    private const DEFAULT_HEIGHT = -1;

    /**
     * @param ImageResource $image
     * @param int           $width
     * @param int|null      $height
     * @return bool|resource
     */
    public function scale(ImageResource $image, int $width, ?int $height = self::DEFAULT_HEIGHT)
    {
        $result = imagescale($image->getResource(), $width, empty($height) ? self::DEFAULT_HEIGHT : $height);

        return $result;
    }
}