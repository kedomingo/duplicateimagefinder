<?php declare(strict_types = 1);

namespace DIF\Models;

final class ImageResource
{
    private $img;

    /**
     * @var ImageColor
     */
    private $totalColorAverage;

    /**
     * ImageResource constructor.
     *
     * @param $gdresource
     */
    public function __construct($gdresource)
    {
        $this->img = $gdresource;
    }

    /**
     * @param ImageColor $color
     */
    public function setTotalColorAverage(ImageColor $color) : void
    {
        $this->totalColorAverage = $color;
    }

    /**
     * @return ImageColor
     */
    public function getTotalColorAverage() : ImageColor
    {
        return $this->totalColorAverage;
    }

    /**
     * @param $img
     */
    public function setResource($img)
    {
        $this->img = $img;
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->img;
    }

    /**
     * @return false|int
     */
    public function getWidth()
    {
        return imagesx($this->img);
    }

    /**
     * @return false|int
     */
    public function getHeight()
    {
        return imagesy($this->img);
    }

    public function getColorAt($x, $y) : ImageColor
    {
        return new ImageColor($this->img, imagecolorat($this->img, $x, $y));
    }

}