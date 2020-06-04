<?php

class ImageComparator
{
    /**
     * Keep track of known scores between 2 hashes. Prevent re-calculation of duplicate files
     * @var array
     */
    private $knownScores = [];

    // height score contributes to 10% of the total score
    private const HEIGHT_SCORE_WEIGHT = 10;

    // color score contributes to 60% of the total score
    private const COLOR_SCORE_WEIGHT = 90;

    /**
     * @param FileResource $file1
     * @param FileResource $file2
     * @return float
     */
    public function compare(FileResource $file1, FileResource $file2)
    {
        $hash1 = $file1->getUniqueIdentifier();
        $hash2 = $file2->getUniqueIdentifier();

        // if both are the same duplicates, return max value
        if (!empty($hash1) && $hash1 === $hash2) {
            // echo "Skipping " . $file2->getName() . " because duplicate\n";
            return 1;
        }
        if (isset($this->knownScores[$hash1][$hash2]) || isset($this->knownScores[$hash2][$hash1])) {
            // echo "Skipping " . $file2->getName() . " because score is known\n";
            return $this->knownScores[$hash1][$hash2] ?? $this->knownScores[$hash2][$hash1];
        }

        $image1      = $file1->getImageResource();
        $image2      = $file2->getImageResource();
        $heightScore = $this->compareHeights($image1, $image2);
        $colorsScore = $this->compareImageColors($image1, $image2);
        $score       = (($heightScore * self::HEIGHT_SCORE_WEIGHT) + ($colorsScore * self::COLOR_SCORE_WEIGHT)) / 100;

        $this->knownScores[$hash1][$hash2] = $this->knownScores[$hash2][$hash1] = $score;

        return $score;
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

                $scores[] = $color1->compareTo($color2);
            }
        }

        return array_sum($scores) / count($scores);
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