<?php declare(strict_types = 1);

namespace DIF\Services;

use DIF\Models\FileResource;

interface ImageComparatorInterface
{
    /**
     * Compare two file resources and return their similarity score
     *
     * @param FileResource $file1
     * @param FileResource $file2
     *
     * @return float Return a value <= 1. The closer they are to 1, the more similar they are
     */
    public function compare(FileResource $file1, FileResource $file2) : float;
}
