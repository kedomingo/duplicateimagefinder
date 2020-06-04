<?php declare(strict_types = 1);

namespace DIF\Services;

use DIF\Factory\ImageResourceFactory;
use DIF\Models\ImageColor;
use DIF\Models\ImageResource;

final class ColorAverager implements ColorAveragerInterface
{
    private const PIXEL_WIDTH = 1;

    /**
     * @var ImageResizerInterface
     */
    private $imageResizer;

    /**
     * ColorAverager constructor.
     *
     * @param ImageResizerInterface $imageResizer
     */
    public function __construct(ImageResizerInterface $imageResizer)
    {
        $this->imageResizer = $imageResizer;
    }

    /**
     * @param ImageResource $imageResource
     * @return ImageColor
     */
    public function getAverageColor(ImageResource $imageResource) : ImageColor
    {
        // Resize to 1x1 pixel to use as total average
        $gdResource = $this->imageResizer->scale($imageResource, self::PIXEL_WIDTH, self::PIXEL_WIDTH);
        $pixel      = ImageResourceFactory::createFromGDResource($gdResource);

        return $pixel->getColorAt(0, 0);
    }
}
