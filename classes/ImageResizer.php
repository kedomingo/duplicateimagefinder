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
        return ImageResource::createFromResource(imagescale($image->getResource(), $width));
    }
}