<?php

namespace DIF\Models;

class ImageColor
{
    private const COLOR_INDEX_RED   = 'red';
    private const COLOR_INDEX_GREEN = 'green';
    private const COLOR_INDEX_BLUE  = 'blue';
    private const COLOR_INDEX_ALPHA = 'alpha';

    /**
     * @var int
     */
    private $red;

    /**
     * @var int
     */
    private $green;

    /**
     * @var int
     */
    private $blue;

    /**
     * @var int
     */
    private $alpha;

    /**
     * ImageColor constructor.
     * @param     $img
     * @param int $rgb
     */
    public function __construct($img, int $rgb)
    {
        $result      = imagecolorsforindex($img, $rgb);
        $this->red   = $result[self::COLOR_INDEX_RED];
        $this->green = $result[self::COLOR_INDEX_GREEN];
        $this->blue  = $result[self::COLOR_INDEX_BLUE];
        $this->alpha = $result[self::COLOR_INDEX_ALPHA];
    }

    /**
     * @return mixed
     */
    public function getRed()
    {
        return $this->red;
    }

    /**
     * @return mixed
     */
    public function getGreen()
    {
        return $this->green;
    }

    /**
     * @return mixed
     */
    public function getBlue()
    {
        return $this->blue;
    }

    /**
     * @return string
     */
    public function getRGBSequence() : string
    {
        return str_pad($this->getRed(), 3, '0')
            . str_pad($this->getGreen(), 3, '0')
            . str_pad($this->getBlue(), 3, '0');
    }

    /**
     * @return mixed
     */
    public function getAlpha()
    {
        return $this->alpha;
    }
}