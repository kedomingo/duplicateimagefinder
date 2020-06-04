<?php declare(strict_types = 1);

namespace DIF\Models;

final class ImageColor
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
     * @param resource $img
     * @param int      $rgb
     */
    public function __construct($img, int $rgb)
    {
        $result      = imagecolorsforindex($img, $rgb);
        $this->red   = (int)$result[self::COLOR_INDEX_RED];
        $this->green = (int)$result[self::COLOR_INDEX_GREEN];
        $this->blue  = (int)$result[self::COLOR_INDEX_BLUE];
        $this->alpha = $result[self::COLOR_INDEX_ALPHA];
    }

    /**
     * @return int
     */
    public function getRed() : int
    {
        return $this->red;
    }

    /**
     * @return int
     */
    public function getGreen() : int
    {
        return $this->green;
    }

    /**
     * @return int
     */
    public function getBlue() : int
    {
        return $this->blue;
    }

    /**
     * @return string
     */
    public function getRGBSequence() : string
    {
        return str_pad((string)$this->getRed(), 3, '0')
            . str_pad((string)$this->getGreen(), 3, '0')
            . str_pad((string)$this->getBlue(), 3, '0');
    }

    /**
     * @return mixed
     */
    public function getAlpha()
    {
        return $this->alpha;
    }
}