<?php declare(strict_types = 1);

namespace DIF\Services;

final class ClosenessComparator implements ClosenessComparatorInterface {

    /**
     * Closeness function. Returns 1 if both numbers are equal, otherwise return a non-zero number less than 1.
     * Do not return zero to prevent total disregard of other scores, if the scores are multiplied with each other
     *
     * @param int|float $num1
     * @param int|float $num2
     *
     * @return float
     */
    public function compare($num1, $num2) : float
    {
        // Both 0, equal
        if ($num1 - $num2 === 0) {
            return 1.0;
        }
        // One is zero, avoid division by zero
        $num1 = $num1 !== 0 ? $num1 : 0.01;
        $num2 = $num2 !== 0 ? $num2 : 0.01;

        return $num1 <= $num2 ? $num1 / $num2 : $num2 / $num1;
    }
}
