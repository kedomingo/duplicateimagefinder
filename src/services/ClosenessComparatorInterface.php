<?php declare(strict_types = 1);

namespace DIF\Services;

interface ClosenessComparatorInterface {

    public function compare($num1, $num2) : float;

}
