<?php declare(strict_types = 1);

namespace DIF\Factory;

use DIF\Exception\UnsupportedImageException;
use DIF\Models\FileResource;
use DIF\Services\ColorAveragerInterface;
use DIF\Services\ImageResizerInterface;

final class FileResourceFactory
{
    private const DEFAULT_SCALE_WIDTH = 32;
    private const PIXEL_WIDTH         = 1;

    /**
     * @var ImageResizerInterface
     */
    private $imageResizer;

    /**
     * @var ColorAveragerInterface
     */
    private $colorAverager;

    /**
     * FileResourceFactory constructor.
     *
     * @param ImageResizerInterface  $imageResizer
     * @param ColorAveragerInterface $colorAverager
     */
    public function __construct(ImageResizerInterface $imageResizer, ColorAveragerInterface $colorAverager)
    {
        $this->imageResizer  = $imageResizer;
        $this->colorAverager = $colorAverager;
    }

    /**
     * Create the FileResources using the given list of file names.
     *
     * @param string ...$filenames
     *
     * @return FileResource[]
     */
    public function getFileResources(string ...$filenames) : array
    {
        $fileInfos = [];
        foreach ($filenames as $filename) {
            $fileInfos[] = new FileResource($filename);
        }

        $filesAndExactDuplicates = $this->findDuplicateFiles(...$fileInfos);

        $uniqueResources = [];
        foreach ($filesAndExactDuplicates as $k => $file) {
            if (!isset($uniqueResources[$file->getUniqueIdentifier()])) {
                // ONLY CREATE ONE RESOURCE FOR A UNIQUE HASH
                $uniqueResources[$file->getUniqueIdentifier()] = 1;

                try {
                    $image = ImageResourceFactory::createFromFile($file);
                } catch (UnsupportedImageException $e) {
                    unset($filesAndExactDuplicates[$k]);
                    continue;
                }
                // Use a downscaled image as resource
                $gdResource = $this->imageResizer->scale($image, self::DEFAULT_SCALE_WIDTH);
                $image->setResource($gdResource);

                // Get the average scene color
                $sceneColorAverage = $this->colorAverager->getAverageColor($image);
                $image->setTotalColorAverage($sceneColorAverage);

                // LINK THE RESOURCE TO THE FILE and all its duplicates
                $file->setImageResource($image);
            }
        }

        return $filesAndExactDuplicates;
    }

    /**
     * Find exact duplicate files
     *
     * @param FileResource ...$files
     *
     * @return FileResource[]
     */
    private function findDuplicateFiles(FileResource ...$files) : array
    {
        $fileInfos = [];
        // Group files by size
        foreach ($files as $fileInfo) {
            $fileInfos[$fileInfo->getSize()][] = $fileInfo;
        }
        $result = [];
        foreach ($fileInfos as $size => $possibleDuplicates) {
            $possibleduplicateCount = count($possibleDuplicates);
            if ($possibleduplicateCount === 1) {
                $result[] = $possibleDuplicates[0];
                continue;
            }
            $this->identifyActualDuplicatesFromSameSizeFiles($possibleDuplicates);
            $result = array_merge($result, $possibleDuplicates);
        }

        return $result;
    }

    /**
     * @param FileResource[] $files
     */
    private function identifyActualDuplicatesFromSameSizeFiles(&$files)
    {
        $fileCount = count($files);
        for ($i = 0; $i < $fileCount; $i++) {
            for ($j = $i + 1; $j < $fileCount; $j++) {
                /**
                 * @var FileResource[] $files
                 */
                if (empty($files[$i]->getTrueHash())) {
                    $files[$i]->setHash();
                }
                if (empty($files[$j]->getTrueHash())) {
                    $files[$j]->setHash();
                }
                if ($files[$i]->getTrueHash() === $files[$j]->getTrueHash()) {
                    $files[$i]->setDuplicateTo($files[$j]);
                    $files[$j]->setDuplicateTo($files[$i]);
                }
            }
        }
    }
}