<?php

class ImageResizer
{
    private const DEFAULT_HEIGHT = -1;

    /**
     * @param ImageResource $image
     * @param               $width
     * @param null          $height
     * @return bool|resource
     */
    public function scale(ImageResource $image, $width, $height = self::DEFAULT_HEIGHT)
    {
        $result = imagescale($image->getResource(), $width, empty($height) ? self::DEFAULT_HEIGHT : $height);

        return $result;
    }
}