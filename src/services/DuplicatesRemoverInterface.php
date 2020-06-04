<?php

namespace DIF\Services;

use DIF\Models\DuplicateFile;

interface DuplicatesRemoverInterface
{
    /**
     * @param bool          $isPrioritizeMatch
     * @param string        $destinationDir
     * @param DuplicateFile ...$files
     * @return mixed
     */
    public function moveDuplicates(bool $isPrioritizeMatch, string $destinationDir, DuplicateFile ...$files);
}