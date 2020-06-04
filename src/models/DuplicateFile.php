<?php

namespace DIF\Models;

final class DuplicateFile
{
    /**
     * @var string
     */
    private $filename;

    /**
     * @var int
     */
    private $filesize;

    /**
     * @var DuplicateFile[]
     */
    private $alternates;

    /**
     * @var float
     */
    private $score;

    /**
     * If this file is the basis for comparison
     *
     * @var bool
     */
    private $isBasisFile;

    /**
     * DuplicateFile constructor.
     * @param string $filename
     * @param int    $filesize
     * @param bool   $isBasisFile
     */
    public function __construct(string $filename, int $filesize, bool $isBasisFile)
    {
        $this->filename    = $filename;
        $this->filesize    = $filesize;
        $this->isBasisFile = $isBasisFile;
    }

    /**
     * @param string $filename
     * @param int    $size
     * @param float  $score
     * @param bool   $isBasisFile
     */
    public function addDuplicate(string $filename, int $size, float $score, bool $isBasisFile = false)
    {
        $duplicate          = new DuplicateFile($filename, $size, $isBasisFile);
        $duplicate->score   = $score;
        $this->alternates[] = $duplicate;
        // The score of this base file in relation to its duplicates. The score will be the maximum match possible
        $this->score = max($this->score, $duplicate->score);
    }

    /**
     * @return string
     */
    public function getFilename() : string
    {
        return $this->filename;
    }

    /**
     * @return int
     */
    public function getFilesize() : int
    {
        return $this->filesize;
    }

    /**
     * @return DuplicateFile[]
     */
    public function getAlternates() : array
    {
        return $this->alternates ?? [];
    }

    /**
     * @return float
     */
    public function getScore() : float
    {
        return $this->score;
    }

    /**
     * @return bool
     */
    public function isBasisFile() : bool
    {
        return $this->isBasisFile;
    }

}