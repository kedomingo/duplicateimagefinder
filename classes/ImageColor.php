<?php

class ImageColor
{
    private const COLOR_INDEX_RED   = 'red';
    private const COLOR_INDEX_GREEN = 'green';
    private const COLOR_INDEX_BLUE  = 'blue';
    private const COLOR_INDEX_ALPHA = 'alpha';

    private $red;
    private $green;
    private $blue;
    private $alpha;

    /**
     * Keep track of comparisons to prevent recalculation
     *
     * @var array
     */
    private $knownComparisons = [];

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

    private function getRgbString()
    {
        return sprintf('%s,%s,%s,%s', $this->red, $this->green, $this->blue, $this->alpha);
    }

    /**
     * Compares 2 color values. The farther each rgb component is between the 2 images, the lower the score.
     * This is achieved by multiplying the closeness of each component, achieving an exponential drop in score
     * TODO: ALPHA for PNG
     *
     * @param ImageColor $other
     * @return float
     */
    public function compareTo(ImageColor $other) : float
    {
        $rgb1 = $this->getRgbString();
        $rgb2 = $other->getRgbString();

        if (!isset($this->knownComparisons[$rgb1][$rgb2])) {
            $redCloseness   = $this->closeness($this->getRed(), $other->getRed());
            $greenCloseness = $this->closeness($this->getGreen(), $other->getGreen());
            $blueCloseness  = $this->closeness($this->getBlue(), $other->getBlue());
            $alphaCloseness = $this->closeness($this->getAlpha(), $other->getAlpha());

            $this->knownComparisons[$rgb1][$rgb2] = $redCloseness * $greenCloseness * $blueCloseness;
        }

        return $this->knownComparisons[$rgb1][$rgb2];
    }

    /**
     * Closeness function. Returns 1 if both numbers are equal, otherwise return a non-zero number less than 1.
     * Do not return zero to prevent total disregard of other scores, if the scores are multiplied with each other
     *
     * @param $num1
     * @param $num2
     *
     * @return float|int
     */
    private function closeness($num1, $num2)
    {
        // Both 0, equal
        if ($num1 - $num2 === 0) {
            return 1;
        }
        // One is zero, avoid division by zero
        $num1 = $num1 !== 0 ? $num1 : 0.01;
        $num2 = $num2 !== 0 ? $num2 : 0.01;

        return $num1 <= $num2 ? $num1 / $num2 : $num2 / $num1;
    }

}