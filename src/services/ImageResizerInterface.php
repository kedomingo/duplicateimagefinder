<?php declare(strict_types = 1);

namespace DIF\Services;

use DIF\Models\ImageResource;

interface ImageResizerInterface
{
    /**
     * @param ImageResource $image
     * @param int           $width
     * @param int           $height
     *
     * @return bool|resource
     */
    public function scale(ImageResource $image, int $width, ?int $height = -1);
}