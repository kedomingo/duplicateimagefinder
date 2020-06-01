<?php

class ImageComparator
{
    // Downsample image to this wide
    private const COMPARISON_WIDTH = 32;

    // height score contributes to 10% of the total score
    private const HEIGHT_SCORE_WEIGHT = 10;

    // color score contributes to 60% of the total score
    private const COLOR_SCORE_WEIGHT = 90;

    private $resizer;

    /**
     * @var ImageResource[]
     */
    private static $resourceCache;

    /**
     * ImageComparator constructor.
     * @param ImageResizer $resizer
     */
    public function __construct(ImageResizer $resizer)
    {
        $this->resizer = $resizer;
    }

    /**
     * @param $file1
     * @param $file2
     * @return float
     * @throws Exception
     */
    public function compare($file1, $file2)
    {
        $score = $this->compareWithSize($file1, $file2, self::COMPARISON_WIDTH);

        return $score;
    }

    /**
     * @param string $file1
     * @param string $file2
     * @param int    $width
     *
     * @return float|int
     * @throws Exception
     */
    private function compareWithSize(string $file1, string $file2, int $width)
    {
        $img1 = $this->getResource($file1);
        $img2 = $this->getResource($file2);

        // If two files are exactly the same, return immediately with the maximum score
        $score = $this->compareFileSize($file1, $file2);
        if (!empty($score)) {
            return $score;
        }

        $resized1 = $this->resizer->resize($img1, $width);
        $resized2 = $this->resizer->resize($img2, $width);

        $heightScore = $this->compareHeights($resized1, $resized2);
        $colorsScore = $this->compareImageColors($resized1, $resized2);

        return (($heightScore * self::HEIGHT_SCORE_WEIGHT) + ($colorsScore * self::COLOR_SCORE_WEIGHT)) / 100;
    }

    /**
     * @param string $file1
     * @param string $file2
     *
     * @return int
     */
    private function compareFileSize(string $file1, string $file2) : int {

        return md5_file($file1) === md5_file($file2) ? 1 : 0;
    }

    /**
     * @param string $filename
     * @return ImageResource
     * @throws Exception
     */
    private function getResource(string $filename) : ImageResource
    {
        if (isset(static::$resourceCache[$filename])) {
            return static::$resourceCache[$filename];
        }
        if (isset(static::$resourceCache[realpath($filename)])) {
            return static::$resourceCache[realpath($filename)];
        }
        static::$resourceCache[$filename] = ImageResource::createFromFilename($filename);

        return static::$resourceCache[$filename];
    }

    /**
     * Assumption: the 2 images have the same width
     *
     * @param ImageResource $img1
     * @param ImageResource $img2
     *
     * @return float
     */
    private function compareHeights(ImageResource $img1, ImageResource $img2) : float
    {
        $height1 = $img1->getHeight();
        $height2 = $img2->getHeight();

        return $this->closeness($height1, $height2);
    }

    /**
     * Assumption: the 2 images have the same width
     *
     * @param ImageResource $img1
     * @param ImageResource $img2
     * @return float
     */
    private function compareImageColors(ImageResource $img1, ImageResource $img2) : float
    {
        $maxWidth            = $img1->getWidth();
        $maxComparableHeight = min($img1->getHeight(), $img2->getHeight());
        $scores              = [];

        for ($y = 0; $y < $maxComparableHeight; $y++) {
            for ($x = 0; $x < $maxWidth; $x++) {
                $color1 = $img1->getColorAt($x, $y);
                $color2 = $img2->getColorAt($x, $y);

                $scores[] = $this->compareColors($color1, $color2);
            }
        }

        return array_sum($scores) / count($scores);
    }

    /**
     * Compares 2 color values. The farther each rgb component is between the 2 images, the lower the score.
     * This is achieved by multiplying the closeness of each component, achieving an exponential drop in score
     * TODO: ALPHA for PNG
     *
     *
     * @param ImageColor $color1
     * @param ImageColor $color2
     * @return float
     */
    private function compareColors(ImageColor $color1, ImageColor $color2) : float
    {
        $redCloseness   = $this->closeness($color1->getRed(), $color2->getRed());
        $greenCloseness = $this->closeness($color1->getGreen(), $color2->getGreen());
        $blueCloseness  = $this->closeness($color1->getBlue(), $color2->getBlue());
        $alphaCloseness = $this->closeness($color1->getAlpha(), $color2->getAlpha());

        $score = $redCloseness * $greenCloseness * $blueCloseness;

        return $score;
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