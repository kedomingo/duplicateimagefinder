<?php

namespace DIF\Services;

use DIF\Models\DuplicateFile;

interface DuplicateImageFinderInterface
{
    /**
     * @param string $directory
     * @param int    $threshold
     * @return DuplicateFile[]
     */
    public function scan(string $directory, int $threshold) : array;

}