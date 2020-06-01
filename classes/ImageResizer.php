<?php

class ImageResizer
{
    /**
     * @param ImageResource $image
     * @param               $width
     * @return ImageResource
     */
    public function resize(ImageResource $image, $width) : ImageResource
    {
        $image->setResource(imagescale($image->getResource(), $width));

        return $image;
    }
}